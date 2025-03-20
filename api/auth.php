<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($action) {
            case 'register':
                // Валидация данных регистрации
                if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                    throw new Exception('Все поля обязательны для заполнения');
                }

                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Некорректный формат email');
                }

                // Проверка существования пользователя
                $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->fetch()) {
                    throw new Exception('Пользователь с таким email уже существует');
                }

                // Создание пользователя
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$data['name'], $data['email'], $hashedPassword]);

                $response = ['status' => 'success', 'message' => 'Регистрация успешна!'];
                break;

            case 'login':
                // Валидация данных авторизации
                if (empty($data['email']) || empty($data['password'])) {
                    throw new Exception('Все поля обязательны для заполнения');
                }

                // Поиск пользователя
                $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
                $stmt->execute([$data['email']]);
                $user = $stmt->fetch();

                if (!$user || !password_verify($data['password'], $user['password_hash'])) {
                    throw new Exception('Неверный email или пароль');
                }

                // Сохранение в сессию
                $_SESSION['user'] = [
                    'id' => $user['user_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                $response = ['status' => 'success', 'user' => $_SESSION['user']];
                break;

            default:
                throw new Exception('Неизвестное действие');
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>