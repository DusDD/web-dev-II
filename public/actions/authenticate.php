<?php
require_once "../database/user_session.php";
require_once "helper/api_utils.php";

ApiUtils\require_post_values("action", "name", "password");

$action = $_POST["action"];
$username = $_POST["name"];
$password = $_POST["password"];

if ($action != "login" && $action != "register") {
    ApiUtils\send_error("Invalid action value: " . $action, 400);
}

if (UserSession\isLoggedIn()) {
    ApiUtils\send_error("Invalid request: User is already logged in!");
}

if ($action == "login") {
    if (UserSession\login($username, $password)) {
        ApiUtils\send_success();
    } else {
        ApiUtils\send_error("Credentials don't match!", 401);
    }
} else {
    if (UserSession\register($username, $password)) {
        ApiUtils\send_success();
    } else {
        ApiUtils\send_error("Registration failed!", 500);
    }
}
