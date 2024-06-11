<?php
require_once "../database/user_session.php";
require_once "../database/message.php";
require_once "helper/api_utils.php";

$user_id = ApiUtils\require_login();
ApiUtils\require_get_values("chat_id");

$chat_id = $_GET["chat_id"];
if (!Chat::chatExists($chat_id)) {
    // send 404 NOT FOUND error
    ApiUtils\send_error("Chat does not exist!", 404);
}

$chat = Chat::getByChatId($chat_id);
if (!$chat->hasUserId($user_id)) {
    // send 401 Forbidden response
    ApiUtils\send_error("User does not have access to this chat!", 401);
}

$data = array();
foreach (Message::loadMessagesForChat($chat_id) as $message) {
    $data[] = array(
        "message" => $message->getContent(),
        "date" => $message->getSentDate(),
        "is_sender" => $message->getSenderId() == $user_id
    );
}
ApiUtils\send_success($data);