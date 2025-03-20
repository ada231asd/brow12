<?php
header('Content-Type: application/json');
require 'backend/ajax/db.php';

$type = $_GET['type'] ?? null;
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

try {
    $query = "SELECT 
        p.product_id,
        p.name AS product_name,
        c.name AS category_name,
        CONCAT('backend/', p.image_url) AS image_url,
        p.is_new,
        p.is_bestseller,
        COALESCE(ROUND(AVG(r.rating)), 0) AS average_rating,
        COUNT(r.review_id) AS reviews_count,
        ROUND(p.price) AS price,
        p.discount,
        ROUND(p.price * (1 - p.discount / 100)) AS final_price
    FROM Products p
    INNER JOIN Categories c ON p.category_id = c.category_id
    LEFT JOIN Reviews r ON p.product_id = r.product_id AND r.status = 'Одобрен'
    WHERE 1=1";

    $params = [];
    
    if ($type === 'hits') {
        $query .= " AND p.is_bestseller = 1";
    } elseif ($type === 'new') {
        $query .= " AND p.is_new = 1";
    }
    
    if ($category_id) {
        $query .= " AND c.category_id = :category_id";
        $params[':category_id'] = $category_id;
    } else {
        $query .= " AND c.category_id IN (1,5)";
    }

    $query .= " GROUP BY p.product_id LIMIT 4";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $products
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}