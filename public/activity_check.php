<?php
session_start();

$timeout_duration = 60; // 1min = 60

if (isset($_SESSION['user_id'])) {
    // Check if the timeout variable is set
    if (isset($_SESSION['last_activity'])) {
        
        $elapsed_time = time() - $_SESSION['last_activity'];
        
        // Check if the elapsed time is greater than the timeout duration
        if ($elapsed_time >= $timeout_duration) {
            $_SESSION = array();
            session_destroy();
            
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            
            header("Location: /login.html");
            exit();
        }
    }
    
    // Update the last activity time stamp
    $_SESSION['last_activity'] = time();
} else {
    // If the user is not logged in, redirect to the login page
    header("Location: /login.html");
    exit();
}
?>
