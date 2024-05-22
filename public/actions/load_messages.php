<?php
include "../database/user_session.php";
include "../database/message.php";


if (!UserSession::isLoggedIn()) {
    // Redirect if user is not logged in
    header("Location: /login.html");
}

if (!isset($_GET["chat_id"])) {
    echo json_encode([]);
    exit("Missing chat_id GET value!");
}

// TODO: error if chat doesn't exist
$data = array();
$user_id = UserSession::getUserId();
$messages = Message::loadMessagesForChat($_GET["chat_id"]);
foreach ($messages as $message) {
    $data[] = array(
        "message" => $message->getContent(),
        "date" => $message->getSentDate(),
        "is_sender" => $message->getSenderId() == $user_id
    );
}
echo json_encode($data);


