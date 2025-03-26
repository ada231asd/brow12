<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

try {
    // Проверка авторизации через куки (как в get_full_user.php)
    if (empty($_COOKIE['auth_token'])) {
        http_response_code(401);
        throw new Exception('Доступ запрещен: требуется авторизация');
    }

    // Получаем user_id по токену
    $stmt = $pdo->prepare("
        SELECT u.user_id 
        FROM User_Sessions s
        JOIN Users u ON s.user_id = u.user_id
        WHERE s.token = ? AND s.expires_at > NOW()
    ");
    $stmt->execute([$_COOKIE['auth_token']]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        throw new Exception('Недействительная сессия. Пожалуйста, войдите снова.');
    }

    $userId = $user['user_id'];

    // Получение данных из запроса
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['product_id'])) {
        throw new Exception('Не указан ID товара');
    }
    $productId = (int)$input['product_id'];

    // Проверка существования товара
    $stmt = $pdo->prepare("SELECT product_id FROM Products WHERE product_id = ?");
    $stmt->execute([$productId]);
    if (!$stmt->fetch()) {
        throw new Exception('Товар не найден');
    }

    // Проверка, добавлен ли товар уже в избранное
    $stmt = $pdo->prepare("SELECT favorite_id FROM Favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $isFavorite = $stmt->fetch();

    if ($isFavorite) {
        // Удаление из избранного
        $stmt = $pdo->prepare("DELETE FROM Favorites WHERE favorite_id = ?");
        $stmt->execute([$isFavorite['favorite_id']]);
        $action = 'removed';
    } else {
        // Добавление в избранное
        $stmt = $pdo->prepare("INSERT INTO Favorites (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        $action = 'added';
    }

    $response = [
        'status' => 'success',
        'action' => $action,
        'message' => $action === 'added' ? 'Товар добавлен в избранное' : 'Товар удален из избранного'
    ];

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
} catch (Exception $e) {
    if (!isset($http_response_code)) {
        http_response_code(400);
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>