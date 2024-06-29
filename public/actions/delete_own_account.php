<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    exit("User is not logged in!");
}

if (!isset($_SESSION["user_id"])) {
    exit("Session parameter user_id is not set!");
}

$user_id = $_SESSION["user_id"];

require_once "../database/database.php";

// Get all chats containing this user
$db = Database::getConnection();
$stmt = $db->prepare("SELECT DISTINCT chat_id FROM user_chat_mappings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$chat_ids = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chat_ids[] = $row["chat_id"];
    }
}
$stmt->close();

// Delete all messages and count the number of deleted messages
$msg_count = 0;
$msg_del_query = $db->prepare("DELETE FROM messages WHERE chat_id = ?");
foreach ($chat_ids as $chat_id) {
    $msg_del_query->bind_param("i", $chat_id);
    $msg_del_query->execute();
    $msg_count += $msg_del_query->affected_rows;
}
$msg_del_query->close();

// Delete user chat mappings
$mapping_count = 0;
$map_del_query = $db->prepare("DELETE FROM user_chat_mappings WHERE chat_id = ?");
foreach ($chat_ids as $chat_id) {
    $map_del_query->bind_param("i", $chat_id);
    $map_del_query->execute();
    $mapping_count += $map_del_query->affected_rows;
}
$map_del_query->close();

// Delete the chats (count deleted chats just to be sure it matches the number of chats)
$chat_count = 0;
$chat_del_query = $db->prepare("DELETE FROM chats WHERE id = ?");
foreach ($chat_ids as $chat_id) {
    $chat_del_query->bind_param("i", $chat_id);
    $chat_del_query->execute();
    $chat_count += $chat_del_query->affected_rows;
}
$chat_del_query->close();

// Delete the user
$user_del_query = $db->prepare("DELETE FROM users WHERE id = ?");
$user_del_query->bind_param("i", $user_id);
$user_del_query->execute();
$is_user_deleted = $user_del_query->affected_rows;
$user_del_query->close();

// Log the user out after deletion
$_SESSION = array();
session_destroy();

// Return the deletion metrics
$result = array(
    "is_user_deleted" => $is_user_deleted,
    "deleted_chats" => $chat_count,
    "deleted_mappings" => $mapping_count,
    "deleted_messages" => $msg_count
);
echo json_encode($result);
exit();