<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    exit("User is not logged in!");
}

if (!$_SESSION["admin"]) {
    exit("Only an admin can delete a user!");
}

require_once "../../database/database.php";

// Query all users
$db = Database::getConnection();
$result = $db->query("SELECT id, username, is_admin FROM users");
$users = array();
// Assume at least 1 user exists
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Return data as json
echo json_encode($users);
exit();