<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dbHost = 'localhost';
        $dbName = 'math_quest';
        $dbUser = 'root';
        $dbPass = '';

        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        } catch (PDOException $e) {
            exit("Database Error: " . htmlspecialchars($e->getMessage()));
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}

function db() {
    return Database::getInstance();
}