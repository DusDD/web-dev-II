<?php
require_once "database.php";
require_once "user_session.php";
require_once "message.php";
require_once "user.php";

abstract class Chat
{

    protected int $chat_id;

    protected function __construct(int $chat_id)
    {
        $this->chat_id = $chat_id;
    }

    protected function getParticipants(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT users.id, users.username
            FROM user_chat_mappings ucm
            INNER JOIN users ON ucm.user_id = users.id
            WHERE ucm.chat_id = :chat_id
        ");
        $stmt->bindParam(":chat_id", $this->chat_id, PDO::PARAM_INT);
        $stmt->execute();

        $participants = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
           $participants[] = new User($row["id"], $row["username"]);
        }
        return $participants;
    }

    public static function chatExists($chat_id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 1
            FROM chats
            WHERE id = :chat_id
        ");
        $stmt->bindParam(":chat_id", $chat_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public static function getByChatId($chat_id): Chat|false
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT type
            FROM chats
            WHERE id = :chat_id
        ");
        $stmt->bindParam(":chat_id", $chat_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row["type"] == "direct" ? new DirectChat($chat_id) : new GroupChat($chat_id);
    }

    public static function loadChatsForUser(int $userId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                chats.id, chats.type,
                IF(chats.type = 'direct', (
                    SELECT ucm2.user_id
                    FROM user_chat_mappings ucm2
                    WHERE ucm2.chat_id = chats.id AND ucm2.user_id != :user_id
                    LIMIT 1
                ), NULL) AS other_user_id
            FROM chats
            INNER JOIN user_chat_mappings ucm ON chats.id = ucm.chat_id
            WHERE ucm.user_id = :user_id
            ORDER BY chats.last_active DESC 
        ");
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        $chats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['type'] == "direct") {
                // direct chat between 2 users
                $chats[] = new DirectChat($row['id']);
            } else {
                // group chat with an unknown number of participants
                $chats[] = new GroupChat($row['id']);
            }
        }
        return $chats;
    }
    public function hasUserId(int $user_id): bool
    {
        foreach ($this->getParticipants() as $participant) {
            if ($participant->getUserId() == $user_id) {
                return true;
            }
        }
        return false;
    }

    public function hasUser(User $user): bool
    {
        return $this->hasUserId($user->getUserId());
    }

    public function getChatId(): int
    {
        return $this->chat_id;
    }

    abstract public function getTitle(): string;

    public function getMessages(): array
    {
        return Message::loadMessagesForChat($this->chat_id);
    }

    public function sendMessage(string $content): void
    {
        $db = Database::getConnection();
        $sender_id = UserSession\getUserId();

        // create new message in database
        $insert_stmt = $db->prepare("
            INSERT INTO messages (chat_id, sender_id, content) 
            VALUES (:chat_id, :sender_id, :content)
        ");
        $insert_stmt->bindParam(":chat_id", $this->chat_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(":sender_id", $sender_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(":content", $content);
        $insert_stmt->execute();

        // update last_active timestamp
        $update_stmt = $db->prepare("
            UPDATE chats 
            SET last_active = NOW()
            WHERE id = :chat_id
        ");
        $update_stmt->bindParam(":chat_id", $this->chat_id, PDO::PARAM_INT);
        $update_stmt->execute();

        // reload messages
        $this->getMessages();
    }
}

class DirectChat extends Chat
{
    private User $other_user;

    public function __construct(int $chat_id)
    {
        parent::__construct($chat_id);

        $participants = $this->getParticipants();
        assert(count($participants) == 2, "DirectChat has invalid number of participants!");

        // figure out what participant is not the logged-in user
        $user1 = $participants[0];
        $user2 = $participants[1];
        $this->other_user = $user1->getUserId() == UserSession\getUserId() ? $user2 : $user1;
    }


    public function getTitle(): string
    {
        return $this->other_user->getUsername();
    }
}

class GroupChat extends Chat
{

    public function __construct(int $chat_id)
    {
        parent::__construct($chat_id);
    }


    public function getTitle(): string
    {
        $title = "";
        foreach ($this->getParticipants() as $participant) {
            // Add separator if the title already contains a username
            if ($title !== "") {
                $title .= ", ";
            }
            $title .= $participant->getUsername();
        }

        return $title;
    }

    public function addUser(User $user): bool
    {
        // make sure the user is not already part of the group chat
        $userId = $user->getUserId();
        foreach ($this->getParticipants() as $participant) {
            if ($participant->getUserId() == $userId) {
                return false;
            }
        }

        // map the chat to the new user
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO user_chat_mappings (user_id, chat_id) VALUES (:user_id,:chat_id)");
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":chat_id", $this->chat_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
