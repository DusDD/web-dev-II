<?php
require_once "../database/chat.php";
require_once "helper/api_utils.php";

$user_id = ApiUtils\require_login();
ApiUtils\require_post_values("message", "chat_id");

$chat_id = $_POST["chat_id"];
if (!Chat::chatExists($chat_id)) {
    ApiUtils\send_error("Invalid chat id!", 400);
}

$chat = Chat::getByChatId($chat_id);
if (!$chat->hasUserId($user_id)) {
    ApiUtils\send_error("User not part of given chat!", 403);
}

$chat->sendMessage($_POST["message"]);
ApiUtils\send_success();