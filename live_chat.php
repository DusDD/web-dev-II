<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect user to login page if no login data is set
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: /public/login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Configura la codificación de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Convo</title> <!-- Define el título de la página -->
    <link rel="stylesheet" href="/public/style/jquery-ui.css">
    <link rel="stylesheet" href="/public/style/style.css"> <!-- Enlaza el archivo CSS para estilos -->
    <link rel="icon" href="/public/images/favicon.png" type="image/x-icon"> <!-- Enlaza el favicon -->
</head>
<body>
    <div class="container">
        <div class="chat-list__container">
            <h2>Users</h2> <!-- Encabezado de la lista de usuarios -->
            <span class="new-chat__container">
                <input type="text" name="new-chat" class="new-chat__input" placeholder="Search for a user...">
                <button id="new-chat__button">Chat</button>
            </span>
        </div>
        <div class="chat">
            <div class="chat_with">Lukas</div>
            <div class="message-container">
                <!-- Aquí se insertarán los mensajes -->
            </div>
            <form id="new-message__form" action="#">
            <textarea id="new-message__input" name="message" placeholder="Type your message"></textarea>
            <button id="send-message__button" type="submit">Send</button>
            </form>
        </div>
    </div>

<!-- Load JQuery Libraries before live_chat.js! -->
<script src="/public/scripts/jquery-3.7.1.js"></script>
<script src="/public/scripts/jquery-ui.js"></script>

<!-- Load the javascript functionality for this site -->
<script src="/public/live_chat.js"></script>

</body>
</html>