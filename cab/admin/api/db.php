<?php
require_once __DIR__ . '/../config/config.php';

class Database {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Ошибка подключения к БД: ' . $e->getMessage()]));
        }
    }

    public function getPdo() {
        return $this->pdo;
    }
}

$db = new Database();
$pdo = $db->getPdo();