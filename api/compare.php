<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => ''];

try {
    if (empty($_SESSION['user']['id'])) {
        throw new Exception('Требуется авторизация');
    }
    
    $userId = $_SESSION['user']['id'];
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = $input['product_id'] ?? null;
    $action = $input['action'] ?? 'add';

    if (!$productId || !is_numeric($productId)) {
        throw new Exception('Некорректный ID товара');
    }

    // Начинаем транзакцию только если она не активна
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }

    // Получаем или создаем список сравнений
    $stmt = $pdo->prepare("SELECT comparison_id FROM Comparison_List WHERE user_id = ?");
    $stmt->execute([$userId]);
    $comparison = $stmt->fetch();

    if (!$comparison) {
        $stmt = $pdo->prepare("INSERT INTO Comparison_List (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        $comparisonId = $pdo->lastInsertId();
    } else {
        $comparisonId = $comparison['comparison_id'];
    }

    // Обработка действий
    if ($action === 'add') {
        $stmt = $pdo->prepare("SELECT * FROM Comparison_Items 
            WHERE comparison_id = ? AND product_id = ?");
        $stmt->execute([$comparisonId, $productId]);
        
        if ($stmt->fetch()) {
            throw new Exception('Товар уже в сравнении');
        }

        $stmt = $pdo->prepare("INSERT INTO Comparison_Items 
            (comparison_id, product_id) VALUES (?, ?)");
        $stmt->execute([$comparisonId, $productId]);
        
        $response['status'] = 'success';
        $response['message'] = 'Товар добавлен в сравнение';
    } 
    elseif ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM Comparison_Items 
            WHERE comparison_id = ? AND product_id = ?");
        $stmt->execute([$comparisonId, $productId]);
        
        $response['status'] = 'success';
        $response['message'] = 'Товар удален из сравнения';
    }

    // Фиксируем транзакцию только если она активна
    if ($pdo->inTransaction()) {
        $pdo->commit();
    }

} catch (Exception $e) {
    // Откатываем только если транзакция активна
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>