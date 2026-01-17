<?php
// .env file should define the following constants:
// USERNAME, PASSWORD, HOST, DATABASE
require_once "config.php";

class Database {
    private $username;
    private $password;
    private $host;
    private $database;
    
    private static $instance = null;
    private $conn; 
    
    private function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function connect()
    {
        // Jeśli połączenie już istnieje
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $this->conn = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode"  => "prefer"]
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        }
        catch(PDOException $e) {
            include 'public/views/500.html';
            exit();
        }
    }
}