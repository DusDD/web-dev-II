<?php

require_once "database.php";
require_once "chat.php";

class User
{
    private int $user_id;
    private string $username;

    private array $chats;

    public function __construct(int $user_id, string $username)
    {
        // Assume a user with the provided attributes exists
        $this->user_id = $user_id;
        $this->username = $username;
        $this->chats = [];
    }

    public static function completeUsername(string $partialName): array
    {
        if (!UserSession::isLoggedIn()) {
            return array();
        }

        $db = Database::getConnection();
        $user_id = UserSession::getUserId();
        $search_term = "%" . $db->quote($partialName) . "%";
        $stmt = $db->prepare("
            SELECT username 
            FROM users 
            WHERE id != :user_id AND username LIKE :search_term
       ");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":search_term", $search_term);
        $stmt->execute();

        $usernames = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usernames[] = $row["username"];
        }
        return $usernames;
    }

    public static function usernameExists(string $username): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public static function getByUserId(int $user_id): User|false
    {
        // Search for user with the given username
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT username FROM users WHERE id = :user_id");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            // No user with the given username exists
            return false;
        }

        // Construct a user object with the corresponding user id
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return new User($user_id, $row["username"]);
    }

    public static function getByUsername(string $username): User|false
    {
        // Search for user with the given username
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            // No user with the given username exists
            return false;
        }

        // Construct a user object with the corresponding user id
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return new User($row["id"], $username);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function createDirectChatWith(User $other_user): false|DirectChat
    {
        // ensure no direct chat between the users exists
        if ($this->hasDirectChatWith($other_user)) {
            return false;
        }

        // insert new chat and save the corresponding chat id
        $db = Database::getConnection();
        $db->query("INSERT INTO chats (type) VALUES ('direct')");
        $new_chat_id = $db->lastInsertId();

        // map the chat to this user
        $map_stmt = $db->prepare("INSERT INTO user_chat_mappings (user_id, chat_id) VALUES (:user_id,:chat_id)");
        $map_stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
        $map_stmt->bindParam(":chat_id", $new_chat_id, PDO::PARAM_INT);
        $map_stmt->execute();

        // map the chat to other user
        $userId = $other_user->getUserId();
        $map_stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $map_stmt->execute();

        // create and return new chat object
        $new_chat = new DirectChat($new_chat_id);
        $this->chats[] = $new_chat;
        return $new_chat;
    }

    public function hasDirectChatWith(User $other_user): bool
    {
        // for each chat of this user, check if it's direct and includes the other user
        foreach ($this->getChats() as $chat) {
            if ($chat instanceof DirectChat && $chat->hasUser($other_user)) {
                return true;
            }
        }

        return false;
    }

    public function getChats(): array
    {
        if (empty($this->chats)) {
            $this->loadChats();
        }
        return $this->chats;
    }

    public function loadChats(): void
    {
        $this->chats = Chat::loadChatsForUser($this->user_id);
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }


}
