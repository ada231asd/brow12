<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

try {
    // Проверка авторизации
    if (empty($_COOKIE['auth_token'])) {
        http_response_code(401);
        throw new Exception('Требуется авторизация');
    }

    // Получаем user_id из сессии
    $stmt = $pdo->prepare("SELECT user_id FROM User_Sessions WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$_COOKIE['auth_token']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(401);
        throw new Exception('Сессия истекла');
    }
    
    $userId = $session['user_id'];

    // Получаем данные из запроса
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['product_id']) || empty($input['rating']) || empty($input['comment'])) {
        throw new Exception('Заполните все поля');
    }
    
    $productId = (int)$input['product_id'];
    $rating = (int)$input['rating'];
    $comment = trim($input['comment']);

    // Валидация рейтинга (1-5)
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Недопустимое значение рейтинга');
    }

    // Проверяем существование товара
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Products WHERE product_id = ?");
    $stmt->execute([$productId]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Товар не найден');
    }

    // Проверяем, не оставлял ли пользователь отзыв ранее
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Вы уже оставляли отзыв на этот товар');
    }

    // Вставка отзыва
    $stmt = $pdo->prepare("
        INSERT INTO Reviews (user_id, product_id, rating, comment, status, created_at)
        VALUES (?, ?, ?, ?, 'На модерации', NOW())
    ");
    $stmt->execute([$userId, $productId, $rating, $comment]);

    $response = [
        'status' => 'success',
        'message' => 'Отзыв отправлен на модерацию'
    ];

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка базы данных';
    error_log($e->getMessage());
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>