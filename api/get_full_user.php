    <?php
    session_start();
    header('Content-Type: application/json');
    require_once __DIR__ . '/../backend/ajax/db.php';

    $response = ['status' => 'error', 'message' => 'Неизвестная ошибка'];

    try {
        // Проверка авторизации
        if (empty($_SESSION['user']['id'])) {
            throw new Exception('Доступ запрещен: требуется авторизация');
        }
        $userId = $_SESSION['user']['id'];

        // Получение данных пользователя
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            throw new Exception('Пользователь не найден');
        }

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
            SELECT ci.*, p.name, p.price 
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

        // Заказы
        $stmt = $pdo->prepare("
            SELECT o.*, oi.product_id, oi.quantity, oi.price_per_item, p.name 
            FROM Orders o
            LEFT JOIN Order_Items oi ON o.order_id = oi.order_id
            LEFT JOIN Products p ON oi.product_id = p.product_id
            WHERE o.user_id = ?
        ");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Группировка товаров в заказах
        $groupedOrders = [];
        foreach ($orders as $item) {
            $orderId = $item['order_id'];
            if (!isset($groupedOrders[$orderId])) {
                $groupedOrders[$orderId] = [
                    'order_id' => $orderId,
                    'order_status' => $item['order_status'],
                    'delivery_address' => $item['delivery_address'],
                    'total_price' => $item['total_price'],
                    'created_at' => $item['created_at'],
                    'items' => []
                ];
            }
            $groupedOrders[$orderId]['items'][] = [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price_per_item' => $item['price_per_item']
            ];
        }
        $result['orders'] = array_values($groupedOrders);

        // Отзывы
        $stmt = $pdo->prepare("SELECT * FROM Reviews WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result['reviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Логи
        $stmt = $pdo->prepare("SELECT * FROM Logs WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result['logs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Рекомендации
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM Recommendations r
            JOIN Products p ON r.product_id = p.product_id
            WHERE r.user_id = ?
        ");
        $stmt->execute([$userId]);
        $result['recommendations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'status' => 'success',
            'data' => $result
        ];

    } catch (PDOException $e) {
        $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    ?>