<?php
session_start();


// Check if the user is logged in
if (isset($_SESSION['user_id'])) {    
    $_SESSION  = array();
    session_destroy();
    
    header("Location: /login.html");
    exit();
}
?>
