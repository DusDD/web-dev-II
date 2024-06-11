<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: /login.html");
    exit();
}

if (!isset($_POST["message_id"])) {
    exit("Missing message_id POST value!");
}
$message_id = $_POST["message_id"];

require_once "../database/database.php";

// Check if the message was sent by the current user
$db = Database::getConnection();
$msg_query = $db->prepare("SELECT 1 FROM messages WHERE id = ? AND sender_id = ?");
$msg_query->bind_param("ii", $message_id, $_SESSION["user_id"]);
$msg_query->execute();
if ($msg_query->get_result()->num_rows == 0) {
    $msg_query->close();
    exit("Access error: Message was not sent by the logged in user!");
}
$msg_query->close();

// Delete the message
$delete_query = $db->prepare("DELETE FROM messages WHERE id = ?");
$delete_query->bind_param("i", $message_id);
$delete_query->execute();

// Return the number of deleted rows (should be 1 if successful)
echo json_encode($delete_query->affected_rows);
$delete_query->close();
exit();