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

require_once "../database/database.php";

// Load messages from the database
$db = Database::getConnection();
$user_id = $_SESSION["user_id"];

// select the chat_id and username of the receiving user for each chat
// if the chat is a group chat username should be null
$chats_query = $db->prepare("
    SELECT DISTINCT ucm.chat_id AS chat_id, users.username AS receiver_name
    FROM user_chat_mappings ucm
    INNER JOIN chats ON ucm.chat_id = chats.id
    LEFT JOIN user_chat_mappings ucm2 
        ON chats.type = 'direct' 
        AND ucm2.chat_id = ucm.chat_id 
        AND ucm2.user_id != ucm.user_id 
    LEFT JOIN users ON ucm2.user_id = users.id
    WHERE ucm.user_id = ?
    ORDER BY chats.last_active
");
$chats_query->bind_param("i", $user_id);
$chats_query->execute();
$chats_result = $chats_query->get_result();

$chats = array();
if($chats_result->num_rows>0) {
    while ($row = $chats_result->fetch_object()) {
        $chat_id = $row->chat_id;
        $receiver = $row->receiver_name;
        $chats[] = array("chat_id" => $chat_id, "name" => $receiver);
    }
}
echo json_encode($chats);