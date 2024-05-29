<?php

// Initialize session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: /login.html");
    exit;
}

require_once "../database/database.php";

if (!isset($_POST["message"])) {
    exit("Missing message value!");
} else if (!isset($_POST["chat_id"])) {
    exit("Missing chat id!");
} else if (!Database::hasUserAccessToChat($_POST["chat_id"])) {
    exit("Access error: User has no chat with id " . $_POST["chat_id"] . "!");
}

$db = Database::getConnection();

// Get the current date
$current_datetime = date('Y-m-d H:i:s');

// Insert the new message into the database
$query = $db->prepare("INSERT INTO messages (chat_id, sender_id, content, sent_date) VALUES (?, ?, ?, ?)");
$query->bind_param('iiss', $_POST["chat_id"], $_SESSION["users_id"], $_POST["message"], $current_datetime);
if (!$query->execute()) {
    exit("Error creating new message!");
}
$query->close();

// Update chat last_active attribute
$query = $db->prepare("UPDATE chat SET last_active = ? WHERE id = ?");
$query->bind_param('si', $current_datetime, $_POST["chats_id"]);
if (!$query->execute()) {
    exit("Error updating chat last_active!");
}
$query->close();
