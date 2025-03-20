<?php
ob_start();
session_start();
header('Content-Type: application/json');
require __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

try {
    if (!$pdo) throw new Exception('Ошибка подключения к БД');

    // Получаем user_id из сессии (может быть null)
    $userId = $_SESSION['user']['id'] ?? null;

    // Параметры запроса
    $filter = $_GET['filter'] ?? null;
    $sort = $_GET['sort'] ?? null;
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $rating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;

    // Базовый запрос с учетом возможного отсутствия пользователя
    $sql = "
        SELECT 
            p.*,
            c.name AS category_name,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(r.review_id) AS reviews_count,
            (p.price * (1 - p.discount / 100)) AS final_price,
            ".($userId ? "EXISTS(
                SELECT 1 
                FROM Favorites f 
                WHERE f.product_id = p.product_id 
                AND f.user_id = :user_id
            )" : "FALSE")." AS is_favorite
        FROM Products p
        LEFT JOIN Categories c ON p.category_id = c.category_id
        LEFT JOIN Reviews r ON p.product_id = r.product_id
    ";

    $whereClauses = [];
    $havingClauses = [];
    $params = [];

    // Фильтрация
    if (in_array($filter, ['hit', 'new'])) {
        $column = $filter === 'hit' ? 'is_bestseller' : 'is_new';
        $whereClauses[] = "p.$column = 1";
    }

    if ($category > 0) {
        $whereClauses[] = "c.category_id = :category";
        $params[':category'] = $category;
    }

    if ($userId) {
        $params[':user_id'] = $userId;
    }

    if (!empty($whereClauses)) {
        $sql .= " WHERE ".implode(" AND ", $whereClauses);
    }

    $sql .= " GROUP BY p.product_id";

    if ($rating > 0) {
        $havingClauses[] = "average_rating >= :rating";
        $params[':rating'] = $rating;
    }

    if (!empty($havingClauses)) {
        $sql .= " HAVING ".implode(" AND ", $havingClauses);
    }

    // Сортировка
    if (in_array($sort, ['price_asc', 'price_desc'])) {
        $order = $sort === 'price_asc' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY final_price $order";
    }

    // Выполнение запроса
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Форматирование данных
    foreach ($products as &$product) {
        $product['is_bestseller'] = (bool)$product['is_bestseller'];
        $product['is_new'] = (bool)$product['is_new'];
        $product['is_favorite'] = (bool)$product['is_favorite'];
        $product['final_price'] = (float)$product['final_price'];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $products
    ]);

} catch (PDOException $e) {
    error_log("Products API Error: ".$e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    ob_end_flush();
}
?>