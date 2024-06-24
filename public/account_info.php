
<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect user to login page if no login data is set
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: /login.html");
    exit();
}

include "main_menue.html";
//include "account_info.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Convo Chat</title>
    <link rel="stylesheet" href="style/main_menue.css">
    <link rel="stylesheet" href="style/live_chat.css">
</head>
<body>
<div>
    <button type="button" style="background-color: chartreuse;">Delete_Button</button>
</div>
</body>
</html>