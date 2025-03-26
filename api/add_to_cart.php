<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

try {
    // Проверка авторизации
    if (empty($_COOKIE['auth_token'])) {
        http_response_code(401);
        throw new Exception('Требуется авторизация');
    }

    // Получаем user_id из сессии
    $stmt = $pdo->prepare("SELECT user_id FROM User_Sessions WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$_COOKIE['auth_token']]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(401);
        throw new Exception('Сессия истекла');
    }
    
    $userId = $session['user_id'];

    // Получаем данные из запроса
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['product_id'])) {
        throw new Exception('Не указан ID товара');
    }
    
    $productId = (int)$input['product_id'];
    $quantity = isset($input['quantity']) ? max(1, (int)$input['quantity']) : 1;

    // Проверяем существование товара через products.php логику
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.price, p.stock_quantity 
        FROM Products p
        WHERE p.product_id = ? AND p.stock_quantity > 0
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Товар недоступен');
    }

    // Получаем или создаем корзину
    $stmt = $pdo->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch();
    
    if (!$cart) {
        $stmt = $pdo->prepare("INSERT INTO Cart (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        $cartId = $pdo->lastInsertId();
    } else {
        $cartId = $cart['cart_id'];
    }

    // Проверяем наличие товара в корзине
    $stmt = $pdo->prepare("
        SELECT cart_item_id, quantity 
        FROM Cart_Items 
        WHERE cart_id = ? AND product_id = ?
    ");
    $stmt->execute([$cartId, $productId]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        // Обновляем количество
        $newQuantity = $cartItem['quantity'] + $quantity;
        if ($newQuantity > $product['stock_quantity']) {
            throw new Exception('Превышено доступное количество');
        }
        
        $stmt = $pdo->prepare("
            UPDATE Cart_Items 
            SET quantity = ? 
            WHERE cart_item_id = ?
        ");
        $stmt->execute([$newQuantity, $cartItem['cart_item_id']]);
    } else {
        // Добавляем новый товар
        $stmt = $pdo->prepare("
            INSERT INTO Cart_Items (cart_id, product_id, quantity) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$cartId, $productId, $quantity]);
    }

    // Возвращаем обновленные данные корзины
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
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cartId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'cart' => [
            'count' => count($cartItems),
            'items' => $cartItems,
            'total' => array_reduce($cartItems, function($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0)
        ],
        'message' => 'Товар добавлен в корзину'
    ];

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Ошибка базы данных';
    error_log($e->getMessage());
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>