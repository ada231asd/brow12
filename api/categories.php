<?php
header('Content-Type: application/json');
require __DIR__ . '/../backend/ajax/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM Categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $categories
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных']);
}