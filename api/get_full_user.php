<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

try {
    // Проверка авторизации через куки
    if (empty($_COOKIE['auth_token'])) {
        http_response_code(401);
        throw new Exception('Доступ запрещен: требуется авторизация');
    }

    // Получаем пользователя по токену
    $stmt = $pdo->prepare("
        SELECT u.* 
        FROM User_Sessions s
        JOIN Users u ON s.user_id = u.user_id
        WHERE s.token = ? AND s.expires_at > NOW()
    ");
    $stmt->execute([$_COOKIE['auth_token']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        http_response_code(401);
        throw new Exception('Недействительная сессия. Пожалуйста, войдите снова.');
    }

    $userId = $userData['user_id'];

    // Сбор связанных данных
    $result = [
        'user' => $userData,
        'cart' => [],
        'comparisons' => [],
        'favorites' => [],
        'orders' => [],
        'reviews' => [],
        'logs' => [],
        'recommendations' => []
    ];

    // Корзина
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.price, p.image_url, p.discount
        FROM Cart_Items ci
        JOIN Products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = (SELECT cart_id FROM Cart WHERE user_id = ?)
    ");
    $stmt->execute([$userId]);
    $result['cart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Сравнения
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM Comparison_Items ci
        JOIN Products p ON ci.product_id = p.product_id
        WHERE ci.comparison_id = (SELECT comparison_id FROM Comparison_List WHERE user_id = ?)
    ");
    $stmt->execute([$userId]);
    $result['comparisons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Избранное
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM Favorites f
        JOIN Products p ON f.product_id = p.product_id
        WHERE f.user_id = ?
    ");
    $stmt->execute([$userId]);
    $result['favorites'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Заказы с группировкой товаров
    $stmt = $pdo->prepare("
        SELECT o.*, oi.product_id, oi.quantity, oi.price_per_item, 
               p.name, p.image_url, ds.status as delivery_status
        FROM Orders o
        LEFT JOIN Order_Items oi ON o.order_id = oi.order_id
        LEFT JOIN Products p ON oi.product_id = p.product_id
        LEFT JOIN Delivery_Status ds ON o.order_id = ds.order_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $groupedOrders = [];
    foreach ($orders as $item) {
        $orderId = $item['order_id'];
        if (!isset($groupedOrders[$orderId])) {
            $groupedOrders[$orderId] = [
                'order_id' => $orderId,
                'order_status' => $item['order_status'],
                'delivery_status' => $item['delivery_status'],
                'delivery_address' => $item['delivery_address'],
                'total_price' => $item['total_price'],
                'created_at' => $item['created_at'],
                'items' => []
            ];
        }
        if ($item['product_id']) {
            $groupedOrders[$orderId]['items'][] = [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'image_url' => $item['image_url'],
                'quantity' => $item['quantity'],
                'price_per_item' => $item['price_per_item']
            ];
        }
    }
    $result['orders'] = array_values($groupedOrders);

    // Отзывы с информацией о товарах
    $stmt = $pdo->prepare("
        SELECT r.*, p.name as product_name, p.image_url
        FROM Reviews r
        JOIN Products p ON r.product_id = p.product_id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$userId]);
    $result['reviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Логи действий
    $stmt = $pdo->prepare("
        SELECT * FROM Logs 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    $result['logs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Рекомендации
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM Recommendations r
        JOIN Products p ON r.product_id = p.product_id
        WHERE r.user_id = ?
        ORDER BY RAND()
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $result['recommendations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // корзина:
    $stmt = $pdo->prepare("
    SELECT 
        ci.cart_item_id,
        ci.product_id,
        ci.quantity,
        p.name,
        p.price,
        p.image_url,
        p.stock_quantity
    FROM Cart_Items ci
    JOIN Products p ON ci.product_id = p.product_id
    WHERE ci.cart_id = (SELECT cart_id FROM Cart WHERE user_id = ?)
");
$stmt->execute([$userId]);
$result['cart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'data' => $result
    ];

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
} catch (Exception $e) {
    if (!isset($response['message'])) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
?>