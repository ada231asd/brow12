<?php
ob_start();
header('Content-Type: application/json');
require __DIR__ . '/../backend/ajax/db.php';

try {
    if (!$pdo) {
        throw new Exception('Ошибка подключения к БД');
    }

    // Получение активных акций
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM Promotions p
        LEFT JOIN Categories c ON p.category_id = c.category_id
        WHERE p.status = 'Активна'
        ORDER BY start_date DESC
    ");
    
    $stmt->execute();
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $promotions
    ]);

} catch (PDOException $e) {
    error_log("Promotions Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка базы данных'
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    ob_end_flush();
}