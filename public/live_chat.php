<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect user to login page if no login data is set
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: /login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Convo Chat</title>
    <link rel="stylesheet" href="style/jquery-ui.css">
    <link rel="stylesheet" href="style/live_chat.css">
</head>
<body>

<!--navigationbar-->
<?php include "main_menue.html"; ?>

<div class="container">
    <div class="chat-list__container">
        <h2>Convo Chats</h2>
        <span class="new-chat__container">
            <input type="text" name="new-chat" class="new-chat__input" placeholder="Search for a user...">
            <button id="new-chat__button">Chat</button>
        </span>

        <ul class="chat-list">
            <!-- Chats will be listed here  -->
        </ul>
    </div>


    <div class="chat-window">
        <h2 class="chat-name">Selected Chat</h2>

        <div class="message-container">
            <!-- Messages of the selected chat will be inserted here -->
        </div>

        <form id="new-message__form" action="#">
            <textarea id="new-message__input" name="message" placeholder="Type your message"></textarea>
            <button id="send-message__button" type="submit">Send</button>
        </form>

    </div>
</div>


<!-- Load JQuery Libraries before live_chat.js! -->
<script src="scripts/jquery-3.7.1.js"></script>
<script src="scripts/jquery-ui.js"></script>

<!-- Load the javascript functionality for this site -->
<script src="scripts/live_chat.js"></script>

</body>
</html>