<?php
require_once "helper/api_utils.php";

$user_id = ApiUtils\require_login();
$chats = array_map(function (Chat $chat) {
    return array(
        "chat_id" => $chat->getChatId(),
        "name" => $chat->getTitle()
    );
}, Chat::loadChatsForUser($user_id));

ApiUtils\send_success($chats);