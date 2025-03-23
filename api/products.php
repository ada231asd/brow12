<?php
header('Content-Type: application/json');
require __DIR__ . '/../backend/ajax/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение параметров
    $params = [
        'rating' => $_GET['rating'] ?? 0,
        'category' => $_GET['category'] ?? null,
        'filter' => $_GET['filter'] ?? null,
        'sort' => $_GET['sort'] ?? null,
        'min_price' => $_GET['min_price'] ?? null,
        'max_price' => $_GET['max_price'] ?? null
    ];

    $query = "
        SELECT 
            p.*,
            c.name AS category_name,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(r.review_id) AS reviews_count,
            (p.price * (1 - p.discount / 100)) AS final_price
        FROM Products p
        LEFT JOIN Categories c ON p.category_id = c.category_id
        LEFT JOIN Reviews r ON p.product_id = r.product_id
    ";

    $where = [];
    $queryParams = [];

    // Фильтр по категории
    if (!empty($params['category'])) {
        $where[] = "c.category_id = :category";
        $queryParams[':category'] = $params['category'];
    }

    // Фильтр по типу (хиты/новинки)
    if (in_array($params['filter'], ['hit', 'new'])) {
        $column = $params['filter'] === 'hit' ? 'is_bestseller' : 'is_new';
        $where[] = "p.$column = 1";
    }

    // Фильтр по цене
    if (!empty($params['min_price'])) {
        $where[] = "(p.price * (1 - p.discount / 100)) >= :min_price";
        $queryParams[':min_price'] = $params['min_price'];
    }

    if (!empty($params['max_price'])) {
        $where[] = "(p.price * (1 - p.discount / 100)) <= :max_price";
        $queryParams[':max_price'] = $params['max_price'];
    }

    // Сборка запроса
    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " GROUP BY p.product_id";

    // Фильтр по рейтингу
    if (!empty($params['rating'])) {
        $query .= " HAVING average_rating >= :rating";
        $queryParams[':rating'] = $params['rating'];
    }

    // Сортировка
    if (in_array($params['sort'], ['price_asc', 'price_desc'])) {
        $order = $params['sort'] === 'price_asc' ? 'ASC' : 'DESC';
        $query .= " ORDER BY final_price $order";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($queryParams);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Форматирование
    foreach ($products as &$product) {
        $product['is_bestseller'] = (bool)$product['is_bestseller'];
        $product['is_new'] = (bool)$product['is_new'];
        $product['final_price'] = (float)number_format($product['final_price'], 2, '.', '');
    }

    echo json_encode([
        'status' => 'success',
        'data' => $products
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}