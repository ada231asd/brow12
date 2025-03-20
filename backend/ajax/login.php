<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start(); // Запуск сессии

include_once('db.php'); // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем email и пароль из POST-запроса
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверка существования пользователя по email
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Устанавливаем сессию для авторизованного пользователя
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role_id'];
        echo json_encode(["success" => true, "message" => "Авторизация успешна"]);
    } else {
        echo json_encode(["success" => false, "message" => "Неверный email или пароль"]);
    }
}
?>
