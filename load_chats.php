<?php

// Initialize sessions
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: login.html");
    exit;
}

require_once "database.php";

// Load messages from the database
$db = Database::getConnection();
$user_id = $_SESSION["user_id"];

$chats_query = $db->prepare("
    SELECT ucm.chat_id AS chat_id, users.username AS receiver_name
    FROM user_chat_mappings ucm
    INNER JOIN chats ON ucm.chat_id = chats.id
    LEFT JOIN user_chat_mappings ucm2 ON chats.type = 'direct' AND ucm2.user_id != ucm.user_id 
    LEFT JOIN users ON ucm2.user_id = users.id
    WHERE ucm.user_id = ?
");
/*$chats_query = $db->prepare("
    SELECT chats.id AS chat_id, users.username AS receiver_name
    FROM chats 
    INNER JOIN user_chat_mappings ON chats.id = user_chat_mappings.chat_id
    LEFT JOIN users ON chats.type = 'direct' AND 
    
    WHERE user_chat_mappings.user_id = ? 
    ORDER BY creation_date DESC
")*/;
$chats_query->bind_param("i", $user_id);
$chats_query->execute();
$chats_result = $chats_query->get_result();

if($chats_result->num_rows>0) {
    while ($row = $chats_result->fetch_object()) {
        $chat_id = $row["chat_id"];
        $receiver = $row["receiver_name"];
        echo "<li class='chat' data-chat-id='{$chat_id}'>{$receiver}</li>";
    }
}