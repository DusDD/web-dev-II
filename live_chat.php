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
    <link rel="stylesheet" href="style/jquery-ui.css">
    <link rel="stylesheet" href="style.css"> <!-- Enlaza el archivo CSS para estilos -->
    <link rel="icon" href="public/images/favicon.png" type="image/x-icon"> <!-- Enlaza el favicon -->
</head>
<body>
    <div class="container">
        <div class="chat-list__container">
            <h2>Users</h2> <!-- Encabezado de la lista de usuarios -->
            <form>
                <input type="text" class="search_users" placeholder="Search users...">
            </form>
            <button id="new-chat__button">Chat</button>
        </div>
        <div class="chat">
            <div class="chat_with">Lukas</div>
            <div class="messages">
                <!-- Aquí se insertarán los mensajes -->
            </div>
            <div class="input-container">
                <input type="text" placeholder="Type your message..."> <!-- Campo de entrada de mensajes -->
                <button id="send-button">Send</button> <!-- Botón de enviar -->
            </div>
        </div>
    </div>

<!-- Load JQuery Libraries before live_chat.js! -->
<script src="scripts/jquery-3.7.1.js"></script>
<script src="scripts/jquery-ui.js"></script>

<!-- Load the javascript functionality for this site -->
<script src="scripts/live_chat.js"></script>

</body>
</html>