<?php
require_once "../database/database.php";

// Load messages from the database
$db = Database::getConnection();
$getusers = $db->prepare("SELECT username FROM users");




?>