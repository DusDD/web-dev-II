<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    exit("User is not logged in!");
}

if (!$_SESSION["admin"]) {
    exit("Only an admin can export data!");
}

require_once "../../database/database.php";
$db = Database::getConnection();

// Collect users
$result = $db->query("SELECT * FROM users");
$users = array();
while($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Collect chats
$result = $db->query("SELECT * FROM chats");
$chats = array();
while($row = $result->fetch_assoc()) {
    $chats[] = $row;
}

// Collect user chat mappings
$result = $db->query("SELECT * FROM user_chat_mappings");
$user_chat_mappings = array();
while($row = $result->fetch_assoc()) {
    $user_chat_mappings[] = $row;
}

// Collect messages
$result = $db->query("SELECT * FROM messages");
$messages = array();
while($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Create result object containing all data and return it as json
$result = array(
    "users" => $users,
    "chats" => $chats,
    "user_chat_mappings" => $user_chat_mappings,
    "messages" => $messages
);
echo json_encode($result);
exit();
