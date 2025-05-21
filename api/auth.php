<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../backend/ajax/db.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

function verifyRecaptcha($recaptchaResponse) {
    $secretKey = "6LdTfkIrAAAAABgSLyBnjNs8YzVuPq5jvBj2yHRr";
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $result = json_decode($response);

    return $result->success;
}

try {
    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($action === 'check') {
            $response = [
                'status' => 'success',
                'csrf_token' => $_SESSION['csrf_token']
            ];
        } else {
            $response['message'] = 'Неизвестное действие';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Проверка reCAPTCHA
        if (!isset($data['g-recaptcha-response']) || !verifyRecaptcha($data['g-recaptcha-response'])) {
            echo json_encode(['status' => 'error', 'message' => 'Пожалуйста, подтвердите, что вы не робот']);
            exit;
        }

        if (is_null($data)) {
            $response['message'] = 'Ошибка парсинга данных';
        } elseif ($action !== 'check' && isset($data['csrf_token']) && $data['csrf_token'] !== $_SESSION['csrf_token']) {
            $response['message'] = 'Неверный CSRF-токен';
        } else {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
            }

            switch ($action) {
                case 'register':
                    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                        $response['message'] = 'Все поля обязательны для заполнения';
                    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        $response['message'] = 'Некорректный формат email';
                    } elseif (!empty($data['phone']) && !preg_match('/^\+7[0-9]{10}$/', $data['phone'])) {
                        $response['message'] = 'Некорректный формат номера телефона';
                    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.{6,})/', $data['password'])) {
                        $response['message'] = 'Пароль должен содержать минимум 6 символов, 1 заглавную букву и 1 спецсимвол (!@#$%^&*)';
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
                        $stmt->execute([$data['email']]);
                        if ($stmt->fetch()) {
                            $response['message'] = 'Пользователь с таким email уже существует';
                        } else {
                            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, role_id, phone, lockout_until) VALUES (?, ?, ?, 2, ?, NULL)");
                            $stmt->execute([$data['name'], $data['email'], $hashedPassword, $data['phone'] ?? null]);

                            $stmt = $pdo->prepare("SELECT u.user_id, u.name, u.email, r.Name AS role FROM Users u JOIN Role r ON u.role_id = r.id WHERE u.email = ?");
                            $stmt->execute([$data['email']]);
                            $user = $stmt->fetch();

                            $response = [
                                'status' => 'success',
                                'message' => 'Регистрация успешна!',
                                'user' => [
                                    'id' => $user['user_id'],
                                    'name' => $user['name'],
                                    'email' => $user['email'],
                                    'role' => $user['role']
                                ]
                            ];
                        }
                    }
                    break;

                case 'login':
                    if (empty($data['email']) || empty($data['password'])) {
                        $response['message'] = 'Все поля обязательны для заполнения';
                    } else {
                        $email = $data['email'];
                        $stmt = $pdo->prepare("SELECT lockout_until FROM Users WHERE email = ?");
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();
                        if ($user && $user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
                            $response['message'] = 'Аккаунт заблокирован до ' . date('Y-m-d H:i:s', strtotime($user['lockout_until'])) . '. Для разблокировки обратитесь в поддержку.';
                        } else {
                            $attempts = isset($_SESSION['login_attempts'][$email]) ? $_SESSION['login_attempts'][$email] : 0;

                            $stmt = $pdo->prepare("SELECT u.*, r.Name AS role FROM Users u JOIN Role r ON u.role_id = r.id WHERE u.email = ?");
                            $stmt->execute([$email]);
                            $user = $stmt->fetch();

                            if (!$user || !password_verify($data['password'], $user['password_hash'])) {
                                $_SESSION['login_attempts'][$email] = $attempts + 1;
                                $stmt = $pdo->prepare("INSERT INTO Logs (user_id, action_description, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE action_description = ?, created_at = NOW()");
                                $stmt->execute([null, "Неудачная попытка входа для $email с IP " . $_SERVER['REMOTE_ADDR'], "Неудачная попытка входа для $email с IP " . $_SERVER['REMOTE_ADDR']]);

                                if ($attempts >= 2) {
                                    $stmt = $pdo->prepare("UPDATE Users SET lockout_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = ?");
                                    $stmt->execute([$email]);
                                    $response['message'] = 'Аккаунт заблокирован на 15 минут. Для разблокировки обратитесь в поддержку.';
                                } else {
                                    $response['message'] = 'Неверный email или пароль';
                                }
                            } else {
                                unset($_SESSION['login_attempts'][$email]);
                                $stmt = $pdo->prepare("UPDATE Users SET lockout_until = NULL WHERE email = ?");
                                $stmt->execute([$email]);
                                $token = bin2hex(random_bytes(32));
                                $expires = time() + 86400;

                                $stmt = $pdo->prepare("INSERT INTO User_Sessions (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))");
                                $stmt->execute([$user['user_id'], $token, $expires]);

                                setcookie('auth_token', $token, [
                                    'expires' => $expires,
                                    'path' => '/',
                                    'secure' => true,
                                    'httponly' => true,
                                    'samesite' => 'Strict'
                                ]);

                                $stmt = $pdo->prepare("INSERT INTO Logs (user_id, action_description, created_at) VALUES (?, ?, NOW())");
                                $stmt->execute([$user['user_id'], "Успешный вход для $email с IP " . $_SERVER['REMOTE_ADDR']]);

                                $response = [
                                    'status' => 'success',
                                    'user' => [
                                        'id' => $user['user_id'],
                                        'name' => $user['name'],
                                        'email' => $user['email'],
                                        'role' => $user['role']
                                    ]
                                ];
                            }
                        }
                    }
                    break;

                case 'logout':
                    if (isset($_COOKIE['auth_token'])) {
                        $stmt = $pdo->prepare("DELETE FROM User_Sessions WHERE token = ?");
                        $stmt->execute([$_COOKIE['auth_token']]);
                        setcookie('auth_token', '', [
                            'expires' => time() - 3600,
                            'path' => '/'
                        ]);
                    }
                    $response = ['status' => 'success', 'message' => 'Вы успешно вышли'];
                    break;

                default:
                    $response['message'] = 'Неизвестное действие';
            }
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка базы данных';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>