<?php
include "database.php";

// Validate the form data
if (!isset($_POST["name"], $_POST["password"])) {
    exit("Please enter a valid name and email address!");
}

if (!isset($_POST["action"])) {
    exit("No submit action set!");
}
$action = $_POST["action"];
if ($action != "login" && $action != "register") {
    exit("Invalid action value: " . $action);
}

// Initialize sessions
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getConnection();

if ($action == "login") {
    // Check if an account with the given username exists
    $stmt = $db->prepare("SELECT id, password_hash FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $_POST["name"]);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        exit("Username does not exist!");
    } else {
        // Check if the password matches
        $row = $result->fetch_assoc();
        if (password_verify($_POST["password"], $row["password_hash"])) {
            // Set the session variables
            $_SESSION["logged_in"] = true;
            $_SESSION["username"] = $_POST["name"];
            $_SESSION["id"] = $row["id"];
            echo "Welcome " . $_SESSION["username"] . "!\n";
        } else {
            exit("Incorrect password!");
        }
    }
} else if ($action == "register") {
    // Check if username already exists
    $check_stmt = $db->prepare("SELECT id FROM accounts WHERE username = ?");
    $check_stmt->bind_param("s", $_POST["name"]);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        exit("Username already exists. Please choose a different username.");
    } else {
        // Insert new user into the database
        $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $insert_stmt = $db->prepare("INSERT INTO accounts (username, password_hash) VALUES (?, ?)");
        $insert_stmt->bind_param("ss", $_POST["name"], $password_hash);
        if ($insert_stmt->execute()) {
            echo "Registration successful!\n";
        } else {
            exit("Registration failed. Please try again later.");
        }
    }
}