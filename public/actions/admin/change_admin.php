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

if (!isset($_POST["user_id"])) {
    exit("POST parameter user_id is not set!");
} else if (!isset($_POST["make_admin"])) {
    exit("POST parameter make_admin is not set!");
}
$user_id = $_POST["user_id"];
$make_admin = $_POST["make_admin"];

require_once "../../database/database.php";

// Update admin permission
$db = Database::getConnection();
$update_query = $db->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
$update_query->bind_param("ii", $make_admin, $user_id);
$update_query->execute();
echo json_encode($update_query->affected_rows >= 1);
$update_query->close();
exit();