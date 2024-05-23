<?php
include_once "database.php";
include_once "user.php";

// ensure the current session is loaded
UserSession::loadUserFromSession();

abstract class UserSession
{
    private static User|null $user;

    private function __construct()
    {
    }

    public static function isLoggedIn(): bool
    {
        return isset(self::$user);
    }

    public static function getUser(): User
    {
        return self::$user;
    }

    public static function login(string $username, string $password): bool
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
        self::$user = new User($row["id"], $username);
        self::saveUserToSession();
        return true;
    }

    private static function saveUserToSession(): void
    {
        $_SESSION['user_id'] = self::getUserId();
        $_SESSION["username"] = self::$user->getUsername();
        $_SESSION['user_data'] = serialize(self::$user);
    }

    public static function getUserId(): int
    {
        // user must be logged in or this fails!
        return self::$user->getUserId();
    }

    public static function register(string $username, string $password): bool
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
        $user_id = $db->lastInsertId();
        self::$user = new User($user_id, $username);
        self::saveUserToSession();
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_data']);
        self::$user = null;
    }

    public static function loadUserFromSession(): void
    {
        if (isset($_SESSION['user_id'])) {
            self::$user = unserialize($_SESSION['user_data']);
        }
    }
}

