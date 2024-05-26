<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {    
    $_SESSION  = array();
    session_destroy();
    
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
    header("Location: /login.html");
    exit();
}
?>
