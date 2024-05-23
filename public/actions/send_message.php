<?php
require_once "../database/user_session.php";
require_once "../database/chat.php";

if (!UserSession::isLoggedIn()) {
    // Redirect if user is not logged in
    header("Location: /login.html");
    exit;
}

if (!isset($_POST["message"])) {
    exit("Missing message value!");
} else if (!isset($_POST["chat_id"])) {
    exit("Missing chat id!");
}

$chat = Chat::getByChatId($_POST["chat_id"]);
if (!$chat) {
    exit("Invalid chat id!");
} else if (!$chat->hasUser(UserSession::getUser())) {
    exit("User not part of given chat!");
}

$chat->sendMessage($_POST["message"]);

