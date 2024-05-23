<?php
include "../database/user_session.php";
include "../database/message.php";


if (!UserSession::isLoggedIn()) {
    header("Location: /login.html");
    exit();
}

if (!isset($_GET["chat_id"])) {
    exit("Missing chat_id GET value!");
}

$chat_id = $_GET["chat_id"];
if (!Chat::chatExists($chat_id)) {
    exit("No chat with id ".$chat_id);
}

$data = array();
$user_id = UserSession::getUserId();
$messages = Message::loadMessagesForChat($chat_id);
foreach ($messages as $message) {
    $data[] = array(
        "message" => $message->getContent(),
        "date" => $message->getSentDate(),
        "is_sender" => $message->getSenderId() == $user_id
    );
}
echo json_encode($data);


