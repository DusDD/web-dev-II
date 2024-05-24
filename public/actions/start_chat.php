<?php
require_once "../database/user_session.php";

// Redirect if user is not logged in
if (!UserSession::isLoggedIn()) {
    header("Location: /login.html");
    exit();
}

if (!isset($_POST["type"])) {
    exit("Missing chat type!");
} else if (!isset($_POST["user_ids"])) {
    exit("Missing user ids!");
}

$type = $_POST["type"];
if ($type != "direct" && $type != "group") {
    exit("Invalid chat type!");
}

// get user ids from POST argument, convert values to integers, remove duplicates
$user_ids = explode(",", $_POST["user_ids"]);
$user_ids = array_map('intval', $user_ids);
$user_ids = array_unique($user_ids);
$num_users = count($user_ids);

// make sure the current user is in the array
if (!in_array(UserSession::getUserId(), $user_ids)) {
    exit("Logged in user must be included in the user_ids POST value!");
}

if ($type == "direct" && $num_users != 2) {
    exit("Invalid number of users for a direct chat: " . $num_users);
} else if ($type == "group" && $num_users <= 2) {
    exit("Invalid number of users for a group chat: " . $num_users);
}

if ($type == "direct") {
    // make sure no direct chat between the users exists
    $other_user_id = UserSession::getUserId() == $user_ids[0] ? $user_ids[1] : $user_ids[0];
    $other_user = User::getByUserId($other_user_id);
    foreach (UserSession::getUser()->getChats() as $chat) {
        if ($chat instanceof DirectChat && $chat->hasUser($other_user)) {
            exit("There already exists a direct chat between the 2 users!");
        }
    }
}




// TODO: REPLACE OLD CODE BELOW

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
