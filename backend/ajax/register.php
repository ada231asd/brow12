<?php
ini_set('display_errors', 1);
error_reporting(E_ALL); // Включаем отображение ошибок

include_once('db.php'); // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получение данных из POST-запроса
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хеширование пароля

    // Проверка, существует ли уже пользователь с таким email
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => false, "message" => "Email уже занят"]);
        exit();
    }

    // Вставка нового пользователя в базу данных
    $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $email, $password, 2])) { // Роль по умолчанию - 'user'
        echo json_encode(["success" => true, "message" => "Регистрация успешна"]);
    } else {
        echo json_encode(["success" => false, "message" => "Ошибка при регистрации"]);
    }
}
?>
