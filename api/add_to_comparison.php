<?php
// add_to_comparison.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$transactionStarted = false; // Флаг для отслеживания транзакции

try {
    // 1. Проверка авторизации
    if (empty($_SESSION['user']['id'])) {
        throw new Exception('Требуется авторизация', 401);
    }
    $userId = (int)$_SESSION['user']['id'];

    // 2. Валидация входных данных
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
        throw new Exception('Некорректный ID товара', 400);
    }
    $productId = (int)$data['product_id'];

    // 3. Проверка существования товара
    $stmt = $pdo->prepare("SELECT category_id FROM Products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        throw new Exception('Товар не найден', 404);
    }

    // 4. Начало транзакции
    $transactionStarted = $pdo->beginTransaction();

    // 5. Работа с Comparison_List
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

    // 6. Проверка ограничений
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Comparison_Items WHERE comparison_id = ?");
    $stmt->execute([$comparisonId]);
    $count = $stmt->fetchColumn();

    if ($count >= 3) {
        throw new Exception('Максимум 3 товара для сравнения', 400);
    }

    // 7. Проверка категории
    if ($count > 0) {
        $stmt = $pdo->prepare("SELECT category_id FROM Comparison_Items 
                             JOIN Products USING(product_id) 
                             WHERE comparison_id = ? 
                             LIMIT 1");
        $stmt->execute([$comparisonId]);
        $existingCategory = $stmt->fetchColumn();
        
        if ($existingCategory != $product['category_id']) {
            throw new Exception('Нельзя сравнивать товары разных категорий', 400);
        }
    }

    // 8. Добавление в Comparison_Items
    $stmt = $pdo->prepare("INSERT INTO Comparison_Items (comparison_id, product_id) 
                          VALUES (?, ?)");
    $stmt->execute([$comparisonId, $productId]);

    // Фиксация транзакции
    $pdo->commit();

    $response = [
        'status' => 'success',
        'message' => 'Товар добавлен в сравнение',
        'comparison_id' => $comparisonId
    ];

} catch (PDOException $e) {
    // Откат только если транзакция была начата
    if ($transactionStarted) {
        try {
            $pdo->rollBack();
        } catch (PDOException $rollbackEx) {
            $response['message'] = 'Ошибка отката: ' . $rollbackEx->getMessage();
        }
    }
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
} catch (Exception $e) {
    // Откат только если транзакция была начата
    if ($transactionStarted) {
        try {
            $pdo->rollBack();
        } catch (PDOException $rollbackEx) {
            $response['message'] = 'Ошибка отката: ' . $rollbackEx->getMessage();
        }
    }
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
}

echo json_encode($response);
?>