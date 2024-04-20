<?php

// Initialize session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: login.html");
    exit;
}

if (!isset($_GET["chat_id"])) {
    echo "No chat selected.";
    exit("Missing chat_id GET value!");
}

require_once "database.php";

// Load messages from the database
$db = Database::getConnection();
$chats_query = $db->prepare("
    SELECT sender_id, content, sent_date
    FROM messages
    WHERE chat_id = ?
    ORDER BY sent_date 
");
$chats_query->bind_param("i",  $_GET["chat_id"]);
$chats_query->execute();
$chats_result = $chats_query->get_result();

if($chats_result->num_rows>0) {
    while ($row = $chats_result->fetch_object()) {
        $message = $row->content;
        $date = $row->sent_date;

        if ( $row->sender_id == $_SESSION["user_id"]) {
            echo "<p class='message sender-message' style='background-color: green'>$date: $message</p>";
        } else {
            echo "<p class='message receiver-message' style='background-color: white'>$date: $message</p>";
        }
    }
}


