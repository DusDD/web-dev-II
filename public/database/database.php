<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Load ini config file
$config = parse_ini_file("config.ini", true);
$db_config = $config["database"];

class Database
{

    private static $db;
    private $connection;

    private function __construct()
    {
        global $db_config;
        // Create new connection to database
        $this->connection = new MySQLi(
            $db_config["host"],
            $db_config["user"],
            $db_config["password"],
            $db_config["db_name"],
            $db_config["port"]
        );

        // Check connection
        if ($this->connection->connect_errno) {
            echo "Failed to connect to MySQL: " . $this->connection->connect_error;
            exit("Failed to connect to MySQL: " . $this->connection->connect_error);
        }
        //echo "Connection information: " . $this->connection->host_info . "<br>";
    }

    public static function hasUserAccessToChat($chatId)
    {
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
