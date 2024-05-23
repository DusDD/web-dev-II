<?php
include "../database/user_session.php";
include_once "../database/user.php";

if (!UserSession::isLoggedIn()) {
    header("Location: /login.html");
    exit();
}

if (!isset($_GET["search_string"]) || strlen($_GET['search_string']) == 0) {
    echo json_encode(array());
    exit("Missing search value!");
}

$query = $_GET['search_string'];
$usernames = User::completeUsername($query);
echo json_encode($usernames);
