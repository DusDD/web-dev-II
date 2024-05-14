<?php

require_once "database.php";

class Message {

    private $message_id;
    private $sender_id;
    private $content;
    private $sent_date;

    public function __construct($message_id, $sender_id, $content, $sent_date)
    {
        $this->message_id = $message_id;
        $this->sender_id = $sender_id;
        $this->content = $content;
        $this->sent_date = $sent_date;

    }

    public static function fromMessageId($msg_id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT sender_id, content, sent_date FROM messages WHERE id = :msg_id");
        $stmt->bindParam(":msg_id", $msg_id);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            // chat doesn't exists
            return false;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        try {
            $sent_date = new DateTime($row["sent_date"]);
        } catch (Exception $e) {
            // error parsing date
            return false;
        }

        return new Message($msg_id, $row["sender_id"], $row["content"], $sent_date);
    }
}
