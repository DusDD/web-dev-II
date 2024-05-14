<?php

require_once "database.php";
require_once "message.php";
require_once "user.php";

class Chat
{

    private $chat_id;
    private $type;
    private $creation_date;
    private $last_active;

    private $messages;

    public function __construct($chat_id, $type, $creation_date, $last_active)
    {
        $this->chat_id = $chat_id;
        $this->type = $type;
        $this->creation_date = $creation_date;
        $this->last_active = $last_active;
        $this->messages = array();
    }

    public static function chatExists($chat_id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM chats WHERE id = :chat_id");
        $stmt->bindParam(":chat_id", $chat_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public static function fromChatId($chat_id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT type, creation_date, last_active FROM chats WHERE id = :chat_id");
        $stmt->bindParam(":chat_id", $chat_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            // chat doesn't exists
            return false;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        try {
            $creation_date = new DateTime($row["creation_date"]);
            $last_active = new DateTime($row["last_active"]);
        } catch (Exception $e) {
            // error parsing date
            return false;
        }

        return new Chat($chat_id, $row["type"], $creation_date, $last_active);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function loadMessages()
    {
        $db = Database::getConnection();
        $chats_query = $db->prepare("
            SELECT id, sender_id, content, sent_date
            FROM messages
            WHERE chat_id = :chat_id
            ORDER BY sent_date 
        ");
        $this->messages = array();
        $chats_query->bindParam(":chat_id", $this->chat_id, PDO::PARAM_INT);
        while ($row = $chats_query->fetch(PDO::FETCH_ASSOC)) {
            // TODO: convert sent_date to DateTime
            $this->messages[] = new Message($row["id"], $row["sender_id"], $row["content"], $row["sent_date"]);
        }
    }

    public function sendMessage($sender_id, $content)
    {
        $db = Database::getConnection();
        $insert_stmt = $db->prepare("
            INSERT INTO messages (chat_id, sender_id, content) 
            VALUES (:chat_id, :sender_id, :content)
        ");
        $insert_stmt->bindParam(":chat_id", $this->chat_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(":sender_id", $sender_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(":content", $content);
        return $insert_stmt->execute();
    }
}

class DirectChat extends Chat
{
    private $other_user;

    public function __construct($chat_id, $other_user, $creation_date, $last_active)
    {
        parent::__construct($chat_id, "direct", $creation_date, $last_active);
        $this->other_user = $other_user;
    }

    public static function newChat($user_id) {
        $other_user = User::fromUserId($user_id);
        if (!$other_user) {
            return false;
        }


    }
}

class GroupChat extends Chat {
    public function __construct($chat_id, $creation_date, $last_active)
    {
        parent::__construct($chat_id, "group", $creation_date, $last_active);
    }
}
