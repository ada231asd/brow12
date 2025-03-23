<?php
header('Content-Type: application/json');
require __DIR__ . '/../backend/ajax/db.php';

try {
    if (empty($_GET['query'])) {
        throw new Exception('Пустой поисковый запрос');
    }

    $searchQuery = '%' . $_GET['query'] . '%';
    
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            c.name AS category_name,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(r.review_id) AS reviews_count,
            (p.price * (1 - p.discount / 100)) AS final_price
        FROM Products p
        LEFT JOIN Categories c ON p.category_id = c.category_id
        LEFT JOIN Reviews r ON p.product_id = r.product_id
        WHERE 
            p.name LIKE :query OR
            p.description LIKE :query OR
            c.name LIKE :query
        GROUP BY p.product_id
        ORDER BY p.name
        LIMIT 50
    ");
    
    $stmt->execute(['query' => $searchQuery]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Форматирование идентичное products.php
    foreach ($results as &$product) {
        $product['is_bestseller'] = (bool)$product['is_bestseller'];
        $product['is_new'] = (bool)$product['is_new'];
        $product['final_price'] = (float)number_format($product['final_price'], 2, '.', '');
    }

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}