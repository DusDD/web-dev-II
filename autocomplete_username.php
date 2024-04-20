<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: authenticate.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    exit("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
}

if (!isset($_GET["search_string"]) || strlen($_GET['search_string']) == 0) {
    echo json_encode(array());
    exit("Missing search value!");
}

require_once "database.php";
$db = Database::getConnection();

// Sanitize the input to prevent SQL injection
$search_string = '%' . $db->real_escape_string($_GET['search_string']) . '%';

$query = $db->prepare("SELECT id, username FROM users WHERE id != ? AND username LIKE ?");
$query->bind_param('is', $_SESSION["user_id"], $search_string);
$query->execute();
$result = $query->get_result();

// Fetch usernames
$usernames = array();
while($row = $result->fetch_assoc()) {
    $usernames[] = $row['username'];
}
$query->close();

// Return usernames as JSON
echo json_encode($usernames);


