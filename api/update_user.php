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

    // Получаем user_id из сессии
    $stmt = $pdo->prepare("SELECT user_id FROM User_Sessions WHERE token = ?");
    $stmt->execute([$_COOKIE['auth_token']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(401);
        throw new Exception('Недействительная сессия');
    }

    $userId = $session['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    // Обновление данных
    $stmt = $pdo->prepare("
        UPDATE Users 
        SET 
            name = COALESCE(?, name),
            email = COALESCE(?, email),
            phone = COALESCE(?, phone),
            delivery_address = COALESCE(?, delivery_address)
        WHERE user_id = ?
    ");
    
    $stmt->execute([
        $_POST['name'] ?? null,
        $_POST['email'] ?? null,
        $_POST['phone'] ?? null,
        $_POST['address'] ?? null,
        $userId
    ]);

    $response = ['status' => 'success'];

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка БД: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>