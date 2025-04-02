<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

try {
    if (empty($_COOKIE['auth_token'])) {
        http_response_code(401);
        throw new Exception('Требуется авторизация');
    }

    // Получаем user_id
    $stmt = $pdo->prepare("SELECT user_id FROM User_Sessions WHERE token = ?");
    $stmt->execute([$_COOKIE['auth_token']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(401);
        throw new Exception('Недействительная сессия');
    }

    $userId = $session['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    // Проверка старого пароля
    $stmt = $pdo->prepare("SELECT password_hash FROM Users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!password_verify($_POST['old_password'], $user['password_hash'])) {
        throw new Exception('Неверный старый пароль');
    }

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        throw new Exception('Пароли не совпадают');
    }

    // Обновление пароля
    $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
    $stmt->execute([$newHash, $userId]);

    $response = ['status' => 'success'];

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка БД: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>