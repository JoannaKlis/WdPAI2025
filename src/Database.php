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
        $this->username = getenv("DB_USER");
        $this->password = getenv("DB_PASSWORD");
        $this->host = getenv("DB_HOST");
        $this->database = getenv("DB_NAME");
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
        try {
            $port = getenv("DB_PORT");

            $this->conn = new PDO(
                "pgsql:host=$this->host;port=$port;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode" => "require"]
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        }
        catch(PDOException $e) {
            include 'public/views/errors/500.html';
            exit();
        }
    }
}