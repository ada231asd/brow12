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

    // Получаем user_id по токену
    $stmt = $pdo->prepare("
        SELECT u.user_id 
        FROM User_Sessions s
        JOIN Users u ON s.user_id = u.user_id
        WHERE s.token = ? AND s.expires_at > NOW()
    ");
    $stmt->execute([$_COOKIE['auth_token']]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        throw new Exception('Недействительная сессия. Пожалуйста, войдите снова.');
    }

    $userId = $user['user_id'];

    // Получаем товары в сравнении
    $stmt = $pdo->prepare("
        SELECT 
            p.product_id,
            p.name,
            p.category_id,
            c.name AS category_name,
            p.image_url,
            p.price,
            p.discount
        FROM Comparison_Items ci
        JOIN Products p ON ci.product_id = p.product_id
        JOIN Categories c ON p.category_id = c.category_id
        WHERE ci.comparison_id = (
            SELECT comparison_id FROM Comparison_List WHERE user_id = ?
        )
    ");
    $stmt->execute([$userId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        $response = ['status' => 'success', 'data' => []];
        echo json_encode($response);
        exit;
    }

    // Собираем характеристики для каждого товара
    foreach ($products as &$product) {
        $stmt = $pdo->prepare("
            SELECT pc.name, pcv.value
            FROM Product_Characteristic_Values pcv
            JOIN Product_Characteristics pc ON pcv.characteristic_id = pc.characteristic_id
            WHERE pcv.product_id = ?
        ");
        $stmt->execute([$product['product_id']]);
        $characteristics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $product['characteristics'] = $characteristics;
    }

    $response = [
        'status' => 'success',
        'data' => [
            'products' => $products,
            'max_items' => 4 // Максимум товаров для сравнения
        ]
    ];

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
} catch (Exception $e) {
    if (!isset($http_response_code)) {
        http_response_code(400);
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>