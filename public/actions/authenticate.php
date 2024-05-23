<?php
include "../database/user_session.php";

if (!isset($_POST["action"])) {
    exit("No submit action set!");
}
if (!isset($_POST["name"], $_POST["password"])) {
    exit("Please enter a valid name and email address!");
}

$action = $_POST["action"];
if ($action != "login" && $action != "register") {
    exit("Invalid action value: " . $action);
}
$username = $_POST["name"];
$password = $_POST["password"];


if (UserSession::isLoggedIn()) {
    exit("User is already logged in!");
}

if ($action == "login") {
    if (UserSession::login($username, $password)) {
        header("Location: /live_chat.php");
    } else {
        exit("Login error!");
    }
} else if ($action == "register") {
    if (UserSession::register($username, $password)) {
        header("Location: /live_chat.php");
    } else {
        exit("Registration failed. Please try again later.");
    }
}
