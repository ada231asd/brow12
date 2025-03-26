<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];
$MAX_COMPARISON_ITEMS = 4; // Максимальное количество товаров для сравнения

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

    // Получаем список сравнения пользователя
    $stmt = $pdo->prepare("
        SELECT comparison_id FROM Comparison_List WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $comparisonList = $stmt->fetch();

    // Если у пользователя нет списка сравнения - создаем
    if (!$comparisonList) {
        $stmt = $pdo->prepare("
            INSERT INTO Comparison_List (user_id) VALUES (?)
        ");
        $stmt->execute([$userId]);
        $comparisonId = $pdo->lastInsertId();
    } else {
        $comparisonId = $comparisonList['comparison_id'];
    }

    // Проверяем, добавлен ли уже товар в сравнение
    $stmt = $pdo->prepare("
        SELECT comparison_item_id FROM Comparison_Items 
        WHERE comparison_id = ? AND product_id = ?
    ");
    $stmt->execute([$comparisonId, $productId]);
    $existingItem = $stmt->fetch();

    if ($existingItem) {
        // Удаляем из сравнения
        $stmt = $pdo->prepare("
            DELETE FROM Comparison_Items WHERE comparison_item_id = ?
        ");
        $stmt->execute([$existingItem['comparison_item_id']]);
        $action = 'removed';
    } else {
        // Проверяем лимит товаров в сравнении
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM Comparison_Items 
            WHERE comparison_id = ?
        ");
        $stmt->execute([$comparisonId]);
        $count = $stmt->fetch()['count'];

        if ($count >= $MAX_COMPARISON_ITEMS) {
            throw new Exception("Максимум $MAX_COMPARISON_ITEMS товаров для сравнения");
        }

        // Добавляем в сравнение
        $stmt = $pdo->prepare("
            INSERT INTO Comparison_Items (comparison_id, product_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$comparisonId, $productId]);
        $action = 'added';
    }

    // Получаем обновленный список товаров для сравнения
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM Comparison_Items ci
        JOIN Products p ON ci.product_id = p.product_id
        JOIN Categories c ON p.category_id = c.category_id
        WHERE ci.comparison_id = ?
    ");
    $stmt->execute([$comparisonId]);
    $comparisonProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'action' => $action,
        'count' => count($comparisonProducts),
        'max_items' => $MAX_COMPARISON_ITEMS,
        'products' => $comparisonProducts,
        'message' => $action === 'added' 
            ? 'Товар добавлен в сравнение' 
            : 'Товар удален из сравнения'
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