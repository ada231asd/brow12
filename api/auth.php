<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
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

                // Создание пользователя с ролью по умолчанию (1 - пользователь)
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role_id) VALUES (?, ?, ?, 1)");
                $stmt->execute([$data['name'], $data['email'], $hashedPassword]);

                $response = ['status' => 'success', 'message' => 'Регистрация успешна!'];
                break;

            case 'login':
                if (empty($data['email']) || empty($data['password'])) {
                    throw new Exception('Все поля обязательны для заполнения');
                }

                // Получаем пользователя с ролью
                $stmt = $pdo->prepare("
                    SELECT u.*, r.Name AS role 
                    FROM Users u
                    JOIN Role r ON u.role_id = r.id
                    WHERE u.email = ?
                ");
                $stmt->execute([$data['email']]);
                $user = $stmt->fetch();

                if (!$user || !password_verify($data['password'], $user['password_hash'])) {
                    throw new Exception('Неверный email или пароль');
                }

                // Генерация токена
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 дней
                
                // Сохранение токена в БД
                $stmt = $pdo->prepare("INSERT INTO User_Sessions (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))");
                $stmt->execute([$user['user_id'], $token, $expires]);

                // Установка куки
                setcookie('auth_token', $token, [
                    'expires' => $expires,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                $response = [
                    'status' => 'success',
                    'user' => [
                        'id' => $user['user_id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ];
                break;

            case 'logout':
                if (isset($_COOKIE['auth_token'])) {
                    // Удаление токена из БД
                    $stmt = $pdo->prepare("DELETE FROM User_Sessions WHERE token = ?");
                    $stmt->execute([$_COOKIE['auth_token']]);
                    
                    // Удаление куки
                    setcookie('auth_token', '', [
                        'expires' => time() - 3600,
                        'path' => '/'
                    ]);
                }
                $response = ['status' => 'success', 'message' => 'Вы успешно вышли'];
                break;

            case 'check':
                if (!empty($_COOKIE['auth_token'])) {
                    // Получаем пользователя с ролью
                    $stmt = $pdo->prepare("
                        SELECT u.*, r.Name AS role 
                        FROM User_Sessions s
                        JOIN Users u ON s.user_id = u.user_id
                        JOIN Role r ON u.role_id = r.id
                        WHERE s.token = ? AND s.expires_at > NOW()
                    ");
                    $stmt->execute([$_COOKIE['auth_token']]);
                    $user = $stmt->fetch();

                    if ($user) {
                        $response = [
                            'status' => 'success',
                            'user' => [
                                'id' => $user['user_id'],
                                'name' => $user['name'],
                                'email' => $user['email'],
                                'role' => $user['role']
                            ]
                        ];
                        break;
                    }
                }
                http_response_code(401);
                throw new Exception('Требуется авторизация');

            default:
                throw new Exception('Неизвестное действие');
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>