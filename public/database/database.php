<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Start PHP session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Import credentials
require_once("credentials.php");


class Database
{

    private static $db;
    private $connection;

    private function __construct()
    {
        // OLD: $this->connection = new MySQLi(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        try {
            // Create new connection to database
            $this->connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

            // Make PDO throw an exception if an error occurs
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $ex) {
            // Exit with error message
            exit("Failed to connect to MySQL: " . $ex->getMessage());

        }
    }

    public static function hasUserAccessToChat($chatId)
    {
        if (session_status() == PHP_SESSION_NONE || !isset($_SESSION["user_id"])) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT 1 FROM user_chat_mappings WHERE user_id = :user_id AND chat_id = :chat_id");
        $stmt->bindParam(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
        $stmt->bindParam(":chat_id", $chatId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public static function getConnection()
    {
        if (self::$db == null) {
            self::$db = new Database();
        }
        return self::$db->connection;
    }

    function __destruct()
    {
        $this->connection->close();
    }
}
