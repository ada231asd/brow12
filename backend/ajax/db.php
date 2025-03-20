<?php
// db.php
$host = 'localhost'; // Хост базы данных
$dbname = 'pk-st'; // Имя базы данных ComputerStore
$username = 'root'; // Имя пользователя базы данных
$password = ''; // Пароль к базе данных (если есть)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Could not connect: ' . $e->getMessage());
}

?>
