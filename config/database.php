<?php
// config/database.php

require_once __DIR__ . '/../config.php';

class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    public function __construct() {
        // Use constants defined in config.php
        $this->host = DB_HOST;
        $this->port = DB_PORT;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
    }

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->username, $this->password, $this->options);

                // Set timezone (Egypt time)
                $this->conn->exec("SET time_zone = '+02:00'");

            } catch(PDOException $exception) {
                // Log the error securely without exposing sensitive details to the user
                error_log("Database connection error: " . $exception->getMessage());
                throw new Exception("خطأ في الاتصال بقاعدة البيانات");
            }
        }
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }
}
