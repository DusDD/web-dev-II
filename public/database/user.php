<?php

require_once "database.php";
require "chat.php";

class User
{
    private $user_id;
    private $username;

    private $chats;

    public function __construct($user_id, $username)
    {
        // Assume a user with the given user id really exists
        // TODO: double check for validity
        $this->user_id = $user_id;
        $this->username = $username;
        $this->chats = array();
    }

    public static function register($username, $password)
    {
        if (User::usernameExists($username)) {
            return false;
        }

        // hash the password to prevent leaks
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->execute();

        return true;
    }

    public static function usernameExists($username)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public static function userExists($user_id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE id = :user_id");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public static function fromUserId($user_id)
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

    public static function getByUsername($username)
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


    public function loadChats()
    {
        $db = Database::getConnection();

        $other_user_query = $db->prepare("
            SELECT user_id, username
            FROM user_chat_mappings ucm
            INNER JOIN users ON ucm.user_id = users.id
            WHERE ucm.chat_id = :chat_id AND ucm.user_id != :cur_user_id
        ");

        $chats_query = $db->prepare("
            SELECT chat_id, type, creation_date, last_active
            FROM user_chat_mappings ucm 
            INNER JOIN chats ON ucm.chat_id = chats.id
            WHERE ucm.user_id = :user_id
            ORDER BY last_active DESC 
        ");
        $chats_query->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
        $this->chats = array();
        while ($row = $chats_query->fetch(PDO::FETCH_ASSOC)) {
            if ($row["type"] == "direct") {
                $other_user_query->bindParam(":chat_id", $row["chat_id"], PDO::PARAM_INT);
                $other_user_query->bindParam(":cur_user_id", $this->user_id, PDO::PARAM_INT);
                $other_user_query->execute();
                $user_row = $other_user_query->fetch(PDO::FETCH_ASSOC);
                $other_user = new User($user_row["user_id"], $user_row["username"]);

                $this->chats[] = new DirectChat($row["chat_id"], $other_user, $row["creation_date"], $row["last_active"]);
            } else {
                $this->chats[] = new GroupChat($row["chat_id"], $row["creation_date"], $row["last_active"]);
            }
        }
    }
}
