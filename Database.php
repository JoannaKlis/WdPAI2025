<?php
// .env file should define the following constants:
// USERNAME, PASSWORD, HOST, DATABASE
require_once "config.php";

// todo: singleto można póżniej przenieś do src
class Database {
    private $username;
    private $password;
    private $host;
    private $database;
    // private $conn; // optional: to hold the connection instance

    public function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }

    public function connect()
    {
        try {
            $conn = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database", // connection string
                $this->username,
                $this->password,
                ["sslmode"  => "prefer"]
            );

            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        // not the best idea to die in production code,
        // better: redirect to error page
        catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function disconnect()
    {
        // $this->conn = null;
    }
}