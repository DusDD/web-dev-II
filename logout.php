<?php

// Make sure the user is really loggad in
if (session_status() == PHP_SESSION_ACTIVE) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
