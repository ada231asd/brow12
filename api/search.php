<?php
header('Content-Type: application/json');
require __DIR__ . '/../backend/ajax/db.php';

try {
    $searchQuery = '%' . $_GET['query'] . '%';
    
    $stmt = $pdo->prepare("
        SELECT 
            p.product_id,
            p.name,
            p.price,
            p.discount,
            p.image_url,
            c.name AS category_name,
            (SELECT COUNT(*) FROM Reviews r WHERE r.product_id = p.product_id) AS reviews_count
        FROM Products p
        JOIN Categories c ON p.category_id = c.category_id
        WHERE 
            p.name LIKE :query OR
            p.description LIKE :query OR
            c.name LIKE :query
        ORDER BY p.name
    ");
    
    $stmt->execute(['query' => $searchQuery]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка поиска']);
}