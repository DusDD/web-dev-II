<?php
require_once "../database/user_session.php";


if (!UserSession::isLoggedIn()) {
    // Redirect if user is not logged in
    header("Location: /login.html");
}

$user = UserSession::getUser();
$chats = array();
foreach ( $user->getChats() as $chat) {
    $chats[] = array(
        "chat_id" => $chat->getChatId(),
        "name" => $chat->getTitle()
    );
}
echo json_encode($chats);
