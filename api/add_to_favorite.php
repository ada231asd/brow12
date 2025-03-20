<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

try {
    // Проверка авторизации
    if (empty($_SESSION['user']['id'])) {
        throw new Exception('Доступ запрещен: требуется авторизация');
    }
    $userId = $_SESSION['user']['id'];

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
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>