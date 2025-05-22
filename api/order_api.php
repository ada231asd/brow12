<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
require_once __DIR__ . '/../backend/ajax/db.php';

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Проверка авторизации
        if (empty($_COOKIE['auth_token'])) {
            http_response_code(401);
            throw new Exception('Требуется авторизация');
        }

        // Получаем пользователя
        $stmt = $pdo->prepare("SELECT u.user_id FROM User_Sessions s
                          JOIN Users u ON s.user_id = u.user_id
                          WHERE s.token = ? AND s.expires_at > NOW()");
        $stmt->execute([$_COOKIE['auth_token']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            http_response_code(401);
            throw new Exception('Недействительная сессия');
        }
        $userId = $user['user_id'];

        $pdo->beginTransaction();

        // Получаем данные из POST
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception('Неверный формат данных');
        }

        $address = $input['address'] ?? '';
        $paymentMethod = $input['payment_method'] ?? '';
        $recipientName = $input['recipient_name'] ?? '';
        $recipientSurname = $input['recipient_surname'] ?? '';
        $recipientPhone = $input['recipient_phone'] ?? '';
        $recipientEmail = $input['recipient_email'] ?? '';
        $recipientNoCall = !empty($input['recipient_no_call']) ? 1 : 0;
        $deliveryType = $input['delivery_type'] ?? '';
        $deliveryDate = $input['delivery_date'] ?? '';
        $deliveryTime = $input['delivery_time'] ?? '';
        $selectedStoreId = $input['selected_store_id'] ?? null;

        // Получаем корзину пользователя
        $stmt = $pdo->prepare("SELECT c.cart_id FROM Cart c WHERE c.user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();

        if (!$cart) {
            throw new Exception('Корзина не найдена');
        }

        // Получаем товары из корзины
        $stmt = $pdo->prepare("
            SELECT ci.*, p.price, p.stock_quantity 
            FROM Cart_Items ci 
            JOIN Products p ON ci.product_id = p.product_id 
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cart['cart_id']]);
        $cartItems = $stmt->fetchAll();

        if (empty($cartItems)) {
            throw new Exception('Корзина пуста');
        }

        // Проверяем наличие товаров
        foreach ($cartItems as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                throw new Exception("Товар {$item['product_id']} недоступен в нужном количестве");
            }
        }

        // Создаем заказ
        $stmt = $pdo->prepare("
            INSERT INTO Orders (
                user_id, 
                order_code,
                order_status, 
                delivery_address,
                total_price,
                created_at
            ) VALUES (?, ?, 'Новый', ?, ?, NOW())
        ");

        $totalAmount = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cartItems));

        // Генерируем временный код заказа
        $tempOrderCode = 'ORDER-' . str_pad(time(), 5, '0', STR_PAD_LEFT);

        $stmt->execute([
            $userId,
            $tempOrderCode,
            $address,
            $totalAmount
        ]);

        $orderId = $pdo->lastInsertId();

        // Обновляем код заказа с правильным ID
        $orderCode = 'ORDER-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("UPDATE Orders SET order_code = ? WHERE order_id = ?");
        $stmt->execute([$orderCode, $orderId]);

        // Добавляем товары в заказ
        $stmt = $pdo->prepare("
            INSERT INTO Order_Items (
                order_id, 
                product_id, 
                quantity, 
                price_per_item
            ) VALUES (?, ?, ?, ?)
        ");

        foreach ($cartItems as $item) {
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
        }

        // Очищаем корзину
        $stmt = $pdo->prepare("DELETE FROM Cart_Items WHERE cart_id = ?");
        $stmt->execute([$cart['cart_id']]);

        // Обновляем остатки товаров ОТДЕЛЬНЫМ запросом
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("
                UPDATE Products 
                SET stock_quantity = stock_quantity - ? 
                WHERE product_id = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Заказ успешно оформлен',
            'order_code' => $orderCode
        ]);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка базы данных: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается'
    ]);
} 