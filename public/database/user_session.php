<?php
namespace UserSession;

use Database;
use PDO;
use User;


function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function getUserId(): int
{
    return $_SESSION['user_id'];
}

function getUsername(): string {
    return $_SESSION["username"];
}

function login(string $username, string $password): bool
{
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, password_hash FROM users WHERE username = :username");
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        // no user with the given username exists
        return false;
    }

    // check if the password matches
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!password_verify($password, $row["password_hash"])) {
        return false;
    }

    // save user data to session
    $_SESSION['user_id'] = $row["id"];
    $_SESSION["username"] = $username;
    return true;
}

function register(string $username, string $password): bool
{
    if (User::usernameExists($username)) {
        return false;
    }

    // hash the password to prevent leaks
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // insert the user into the database
    $db = Database::getConnection();
    $stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password_hash", $password_hash);
    $stmt->execute();

    // save user data to session
    $_SESSION['user_id'] =$db->lastInsertId();
    $_SESSION["username"] = $username;
    return true;
}

function logout(): void
{
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
}


