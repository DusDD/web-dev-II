<?php

// Initialize session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: authenticate.php");
    exit;
}

if (!isset($_POST["message"])) {
    exit("Missing message value!");
} else if (!isset($_POST["chat_id"])) {
    exit("Missing chat id!");
}

require_once "database.php";
$db = Database::getConnection();

// Save the message to the database
$db = Database::getConnection(); // Assuming Database class has a static getConnection method

// Get the current date
$current_datetime = date('Y-m-d H:i:s');

// Insert the new message into the database
$query = $db->prepare("INSERT INTO messages (chat_id, sender_id, content, sent_date) VALUES (?, ?, ?, ?)");
$query->bind_param('iiss', $_POST["chat_id"], $_SESSION["user_id"], $_POST["message"], $current_datetime);
if (!$query->execute()) {
    exit("Error creating new message!");
}
$query->close();

// Update chat last_active attribute
$query = $db->prepare("UPDATE chats SET last_active = ? WHERE id = ?");
$query->bind_param('si', $current_datetime, $_POST["chat_id"]);
if (!$query->execute()) {
    exit("Error updating chat last_active!");
}
$query->close();
