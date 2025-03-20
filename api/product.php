<?php
header('Content-Type: application/json');
require __DIR__ . '/../backend/ajax/db.php';
$productId = $_GET['id'] ?? null;

try {
    if (!$productId) throw new Exception("ID товара не указан");

    // Основная информация
    $query = "SELECT 
                p.product_id,
                p.name AS product_name,
                p.description,
                c.name AS category_name,
                CONCAT('backend/', p.image_url) AS image_url,
                p.price,
                p.discount,
                ROUND(p.price * (1 - p.discount / 100)) AS final_price,
                COALESCE(ROUND(AVG(r.rating), 0), 0) AS average_rating, -- Исправлено
                COUNT(r.review_id) AS reviews_count
              FROM Products p
              JOIN Categories c ON p.category_id = c.category_id
              LEFT JOIN Reviews r ON p.product_id = r.product_id AND r.status = 'Одобрен'
              WHERE p.product_id = :product_id
              GROUP BY p.product_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':product_id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) throw new Exception("Товар не найден");

    // Характеристики
    $query = "SELECT 
                pc.name AS characteristic_name,
                pv.value AS characteristic_value
              FROM Product_Characteristic_Values pv
              JOIN Product_Characteristics pc ON pv.characteristic_id = pc.characteristic_id
              WHERE pv.product_id = :product_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':product_id' => $productId]);
    $characteristics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Отзывы
    $query = "SELECT 
                rating,
                comment,
                created_at,
                (SELECT name FROM Users WHERE user_id = Reviews.user_id) AS author
              FROM Reviews
              WHERE product_id = :product_id AND status = 'Одобрен'";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':product_id' => $productId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'product' => $product,
            'characteristics' => $characteristics,
            'reviews' => $reviews
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}