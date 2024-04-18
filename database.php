<?php

// Import credentials
require_once('credentials.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
            exit("Failed to connect to MySQL Database!");
        }
        echo $this->connection->host_info . "\n";
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
