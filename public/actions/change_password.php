<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: /login.html");
    exit();
}

if (!isset($_POST["current-password"]) || !isset($_POST["new-password"]) || !isset($_POST["confirm-password"])) {
    exit("Missing POST values!");
}

$cur_password = $_POST["current-password"];
$new_password = $_POST["new-password"];
$confirm_password = $_POST["confirm-password"];
if ($new_password !== $confirm_password) {
    exit("New password and confirmation password do not match!");
}

// Get the account data
$db = Database::getConnection();
$stmt = $db->prepare("SELECT id, password_hash, is_admin FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    exit("Username does not exist!");
}
// Check if the current password matches
$row = $result->fetch_assoc();
if (!password_verify($cur_password, $row["password_hash"])) {
    exit("Incorrect password!");
}

// verify password criteria
// TODO: put in config
$password_min_length = 8;
if (strlen($new_password) < $password_min_length) {
    exit("Password too short!");
}

// Update password hash in database
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$stmt->bind_param("si", $password_hash, $_SESSION["user_id"]);
if ($stmt->execute()) {
    echo "Password update successful!<br>";
} else {
    exit("Password update failed. Please try again later.");
}