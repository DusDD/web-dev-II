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
    <title>Chats</title>
    <link rel="stylesheet" href="style/jquery-ui.css">
    <link rel="stylesheet" href="style/live_chat.css">
</head>
<body>

    <!--navigationbar-->
    <div class="navbar">
        <ul>
            <a href="main_menue.html"><li>
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="50" height="50" viewBox="0,0,256,256">
                    <g fill="#ffffff" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                        <g transform="scale(10.66667,10.66667)">
                            <path d="M12,2.09961l-11,9.90039h3v9h6v-7h4v7h6v-9h3z"></path>
                        </g>
                    </g>
                </svg>
            </li></a>
            <a href="account_info.php"><li>Account</li></a>
            <a href="logout.php"><li>Logout</li></a>
        </ul>
    </div>

<div class="container">

    <div class="chat-list-container">
        <h2>Chats</h2>
        <span class="new-chat-container">
            <input type="text" name="new-chat" placeholder="Search for a user...">
            <button onclick="location.href='actions/start_chat.php'">Chat</button>
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

        <form action="actions/send_message.php" method="post">
            <input name="message" placeholder="Type your message">
            <button type="submit">Send</button>
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
