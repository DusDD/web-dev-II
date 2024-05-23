<?php

require_once "database.php";
require_once "message.php";
require_once "user.php";

abstract class Chat
{

    protected int $chat_id;
    protected array $participants;

    private array $messages;

    protected function __construct(int $chat_id)
    {
        $this->chat_id = $chat_id;
        $this->participants = array();
        $this->messages = array();
        $this->loadParticipants();
    }

    protected function loadParticipants(): void
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

        $this->participants = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->participants[] = new User($row["id"], $row["username"]);
        }
    }

    public static function chatExists($chat_id): bool {
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

        // check if chat exists
        if ($stmt->rowCount() == 0) {
            return false;
        }
        assert($stmt->rowCount() == 1, "DB has duplicate chat ids!");
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

    public function getParticipants(): array
    {
        return $this->participants;
    }

    public function hasUser(User $user): bool
    {
        $participants_ids = array_map(function (User $u) {
            return $u->getUserId();
        }, $this->participants);

        return in_array($user->getUserId(), $participants_ids);
    }

    public function getChatId(): int
    {
        return $this->chat_id;
    }

    abstract public function getTitle(): string;

    public function getMessages(): array
    {
        if (empty($this->messages)) {
            $this->loadMessages();
        }
        return $this->messages;
    }

    public function loadMessages(): void
    {
        $this->messages = Message::loadMessagesForChat($this->chat_id);
    }

    public function sendMessage(string $content): void
    {
        $db = Database::getConnection();
        $sender_id = UserSession::getUserId();

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
        $this->loadMessages();
    }
}

class DirectChat extends Chat
{
    private User $other_user;

    public function __construct(int $chat_id)
    {
        parent::__construct($chat_id);
        assert(count($this->participants) == 2, "DirectChat has invalid number of participants!");

        // extract id from not logged in user
        $my_user_id = UserSession::getUserId();
        $user1 = $this->participants[0];
        $user2 = $this->participants[1];
        $this->other_user = $user1 == $my_user_id ? $user2 : $user1;
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
        foreach ($this->participants as $user) {
            if ($title !== "") {
                // Add separator if the title already contains a username
                $title .= ", ";
            }
            $title .= $user->getUsername();
        }

        return $title;
    }

    public function addUser(User $user): bool
    {
        // make sure the user is not already part of the group chat
        $userId = $user->getUserId();
        $this->loadParticipants();
        foreach ($this->participants as $participant) {
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
