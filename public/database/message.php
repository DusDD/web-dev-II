<?php

require_once "database.php";

class Message
{

    private int $message_id;
    private int $sender_id;
    private string $content;
    private string $sent_date;

    private function __construct(int $message_id, int $sender_id, string $content, string $sent_date)
    {
        $this->message_id = $message_id;
        $this->sender_id = $sender_id;
        $this->content = $content;
        $this->sent_date = $sent_date;
    }

    public static function loadMessagesForChat(int $chatId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM messages WHERE chat_id = :chat_id");
        $stmt->bindParam(":chat_id", $chatId, PDO::PARAM_INT);
        $stmt->execute();

        $messages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $messages[] = new self($row['id'], $row['sender_id'], $row['content'], $row['sent_date']);
        }
        return $messages;
    }

    public function getSenderId(): int
    {
        return $this->sender_id;
    }

    public function getContent(): int|string
    {
        return $this->content;
    }

    public function getSentDate(): string
    {
        return $this->sent_date;
    }
}
