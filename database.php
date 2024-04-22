<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Import credentials
require_once("credentials.php");

// Enable error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);

class Database
{

    private static $db;
    private $connection;

    private function __construct()
    {
        // Create new connection to database
        $this->connection = new MySQLi(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

        // Check connection
        if ($this->connection->connect_errno) {
            echo "Failed to connect to MySQL: " . $this->connection->connect_error;
            exit("Failed to connect to MySQL: " . $this->connection->connect_error);
        }
        //echo "Connection information: " . $this->connection->host_info . "<br>";
    }

    function __destruct()
    {
        $this->connection->close();
    }

    public static function getConnection()
    {
        if (self::$db == null) {
            self::$db = new Database();
        }
        return self::$db->connection;
    }

    public static function hasUserAccessToChat($chatId) {
        if (!isset($_SESSION) || !isset($_SESSION["user_id"])) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT 1 FROM user_chat_mappings WHERE user_id = ? AND chat_id = ?");
        $stmt->bind_param("ii", $_SESSION["user_id"], $chatId);
        if (!$stmt->execute()) {
            return false;
        }
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}
