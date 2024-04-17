<?php
global $pdo;
include 'database.php';

// Validate the form data
if (!isset($_POST['name'], $_POST['email'])) {
    exit('Please enter a valid name and email address!');
}

$stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
$stmt->execute([ $_POST['email'] ]);