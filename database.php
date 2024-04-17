<?php

// Initialize sessions
session_start();
// Database connection varibles. Change them to reflect your own.
$db_host = '10.35.46.101';
$db_port = '3306';
$db_name = 'k233795_convodb';
$db_user = 'k233795_convodb_admin';
$db_pass = 'Hn0!u8u51';

class Database
{

    private static $db;
    private $connection;

    private function __construct()
    {
        $this->connection = new MySQLi(
            '10.35.46.101',
            'k233795_convodb_admin',
            'Hn0!u8u51',
            'k233795_convodb',
            3306
        );

        echo $this->connection->host_info . "\n";
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

}

$db = Database::getConnection();

/*
try {
    // Attempt to connect to our MySQL database
    $pdo = new PDO('mysql:host=localhost;dbname=' . $db_name . ';charset=utf8', $db_user, $db_pass);
    // Output all connection errors. We want to know what went wrong...
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    // Failed to connect! Check the database variables and ensure your database exists with all tables.
    exit('Failed to connect to database!');
}
*/



