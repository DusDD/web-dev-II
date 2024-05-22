<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    // Redirect if user is not logged in
    header("Location: /login.html");
    exit;
}

if (!isset($_POST["username"])) {
    echo json_encode(array());
    exit("Missing username value!");
}

require_once "../database/database.php";
$db = Database::getConnection();

// Get user id of receiver
$user_query = $db->prepare("SELECT id FROM users WHERE username = ?");
$user_query->bind_param("s", $_POST["username"]);
$user_query->execute();
$result = $user_query->get_result();
if ($result->num_rows == 0) {
    echo "User " . $_POST["username"] . " not found!";
    exit();
}
$user_row = $result->fetch_assoc();
$other_user_id = $user_row["id"];
$user_query->close();

//TODO: check if there is already a chat with the person

// Insert new chat
$success = $db->query("INSERT INTO chats (type) VALUES ('direct')");
if (!$success) {
    exit("Error inserting new chat!");
}
// Save the newly created chat id
$new_chat_id = $db->insert_id;

//TODO check functionality 
// Add participants to chat
$map_query = $db->prepare("INSERT INTO user_chat_mappings (user_id, chat_id) VALUES (?,?)");
$map_query->bind_param("ii", $_SESSION["user_id"], $new_chat_id);
if (!$map_query->execute()) {
    exit("Error creating new chat binding for logged in user!");
}
$map_query->close();

$map_query = $db->prepare("INSERT INTO user_chat_mappings (user_id, chat_id) VALUES (?,?)");
$map_query->bind_param("ii", $other_user_id, $new_chat_id);
if (!$map_query->execute()) {
    exit("Error creating new chat binding for receiving user!");
}
$map_query->close();
