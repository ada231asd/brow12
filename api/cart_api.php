<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Invalid request'];

try {
    // Проверка авторизации
    if (empty($_COOKIE['auth_token'])) {
        http_response_code(401);
        throw new Exception('Authorization required');
    }

    // Получаем пользователя
    $stmt = $pdo->prepare("SELECT u.user_id FROM User_Sessions s
                          JOIN Users u ON s.user_id = u.user_id
                          WHERE s.token = ? AND s.expires_at > NOW()");
    $stmt->execute([$_COOKIE['auth_token']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(401);
        throw new Exception('Invalid session');
    }
    $userId = $user['user_id'];

    // Получаем корзину пользователя
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

    // Обработка методов
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Получение содержимого корзины
        $stmt = $pdo->prepare("SELECT 
                ci.cart_item_id,
                p.product_id,
                p.name,
                p.price,
                p.discount,
                p.image_url,
                p.stock_quantity,
                ci.quantity,
                FLOOR(p.price * (1 - p.discount/100)) as final_price
            FROM Cart_Items ci
            JOIN Products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?");
        $stmt->execute([$cartId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'status' => 'success',
            'data' => $items
        ];
    } 
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Обновление количества товара
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['cart_item_id']) || !isset($input['quantity'])) {
            throw new Exception('Missing parameters');
        }
        
        $cartItemId = (int)$input['cart_item_id'];
        $quantity = (int)$input['quantity'];
        
        // Проверка доступного количества
        $stmt = $pdo->prepare("SELECT p.stock_quantity 
                              FROM Cart_Items ci
                              JOIN Products p ON ci.product_id = p.product_id
                              WHERE ci.cart_item_id = ?");
        $stmt->execute([$cartItemId]);
        $stock = $stmt->fetchColumn();
        
        if ($quantity < 1 || $quantity > $stock) {
            throw new Exception('Invalid quantity');
        }
        
        // Обновление количества
        $stmt = $pdo->prepare("UPDATE Cart_Items SET quantity = ?
                              WHERE cart_item_id = ?");
        $stmt->execute([$quantity, $cartItemId]);
        
        $response = ['status' => 'success'];
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Удаление товара или очистка
        $input = json_decode(file_get_contents('php://input'), true);
        $cartItemId = $input['cart_item_id'] ?? null;
        
        if ($cartItemId) {
            $stmt = $pdo->prepare("DELETE FROM Cart_Items 
                                  WHERE cart_item_id = ?");
            $stmt->execute([$cartItemId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM Cart_Items WHERE cart_id = ?");
            $stmt->execute([$cartId]);
        }
        
        $response = ['status' => 'success'];
    }

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>