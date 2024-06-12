<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: /login.html");
    exit();
}

if (!isset($_POST["chat_id"])) {
    exit("Missing chat_id POST value!");
}
$chat_id = $_POST["chat_id"];

require_once "../database/database.php";
// Make sure the user has access to the chat
if (!Database::hasUserAccessToChat($chat_id)) {
    exit("Access error: User has no chat with id " . $chat_id . "!");
}

// Delete all messages and count the number of deleted messages
$db = Database::getConnection();
$msg_del_query = $db->prepare("DELETE FROM messages WHERE chat_id = ?");
$msg_del_query->bind_param("i", $chat_id);
$msg_del_query->execute();
$msg_count = $msg_del_query->affected_rows;
$msg_del_query->close();

// Delete user chat mappings
$map_del_query = $db->prepare("DELETE FROM user_chat_mappings WHERE chat_id = ?");
$map_del_query->bind_param("i", $chat_id);
$map_del_query->execute();
$mapping_count = $map_del_query->affected_rows;
$map_del_query->close();

// Delete the chat
$chat_del_query = $db->prepare("DELETE FROM chats WHERE id = ?");
$chat_del_query->bind_param("i", $chat_id);
$chat_del_query->execute();
$chat_count = $chat_del_query->affected_rows;
$chat_del_query->close();

// Return the deletion metrics as json
$result = array(
    "deleted_messages" => $msg_count,
    "deleted_mappings" => $mapping_count,
    "deleted_chats" => $chat_count
);
echo json_encode($result);
exit();