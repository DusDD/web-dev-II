<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect user to login page if no login data is set
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: /login.html");
    exit();
}

include_once "activity_check.php";
include "main_menue.html";
include "live_chat.html";

?>