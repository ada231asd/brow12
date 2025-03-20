<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $stats = [];

    // 1. Новые пользователи (24ч)
    $stats['new_users_24h'] = $pdo->query("SELECT COUNT(*) FROM Users WHERE created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn();

    // 2. Активные пользователи (онлайн)
    $stats['active_users'] = $pdo->query("SELECT COUNT(*) FROM Users WHERE status = 'В сети'")->fetchColumn();

    // 3. Топ-3 пользователя по сумме заказов
    $stats['top_users'] = $pdo->query("
        SELECT u.name, SUM(o.total_price) as total 
        FROM Users u 
        JOIN Orders o ON u.user_id = o.user_id 
        GROUP BY u.user_id 
        ORDER BY total DESC 
        LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 4. Пользователи без заказов
    $stats['users_no_orders'] = $pdo->query("
        SELECT COUNT(*) 
        FROM Users u 
        LEFT JOIN Orders o ON u.user_id = o.user_id 
        WHERE o.order_id IS NULL
    ")->fetchColumn();

    // 5. Товары с низким запасом (<10)
    $stats['low_stock'] = $pdo->query("
    SELECT name, stock_quantity 
    FROM Products 
    WHERE stock_quantity < 10 
    ORDER BY stock_quantity ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

    // 6. Средняя цена товаров
    $stats['avg_price'] = $pdo->query("SELECT AVG(price) FROM Products")->fetchColumn();

    // 7. Топ-3 товара по скидкам
    $stats['top_discounts'] = $pdo->query("
        SELECT name, discount 
        FROM Products 
        ORDER BY discount DESC 
        LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 8. Недавно добавленные товары
    $stats['new_products'] = $pdo->query("
        SELECT name 
        FROM Products 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_COLUMN);

    // 9. Средний чек
    $stats['avg_order'] = $pdo->query("SELECT AVG(total_price) FROM Orders")->fetchColumn();

    // 10. Заказы за сегодня
    $stats['today_orders'] = $pdo->query("
        SELECT COUNT(*) 
        FROM Orders 
        WHERE DATE(created_at) = CURDATE()
    ")->fetchColumn();

    // 11. Самый дорогой заказ
    $stats['max_order'] = $pdo->query("SELECT MAX(total_price) FROM Orders")->fetchColumn();

    // 12. Возвраты
    $stats['returns'] = $pdo->query("
        SELECT COUNT(*) 
        FROM Delivery_Status 
        WHERE status = 'Возврат'
    ")->fetchColumn();

    // 13. Выручка за месяц
    $stats['month_revenue'] = $pdo->query("
        SELECT SUM(total_price) 
        FROM Orders 
        WHERE MONTH(created_at) = MONTH(NOW())
    ")->fetchColumn();
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();

    // 14. Топ-3 прибыльных товара
    $stats['top_products'] = $pdo->query("
        SELECT p.name, SUM(oi.price_per_item * oi.quantity) as revenue 
        FROM Order_Items oi 
        JOIN Products p ON oi.product_id = p.product_id 
        GROUP BY p.product_id 
        ORDER BY revenue DESC 
        LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 15. Сумма скидок
    $stats['total_discounts'] = $pdo->query("
        SELECT SUM(p.price * p.discount / 100 * oi.quantity) 
        FROM Order_Items oi 
        JOIN Products p ON oi.product_id = p.product_id 
        WHERE p.discount > 0
    ")->fetchColumn();

    // 16. Неоплаченные заказы
    $stats['unpaid_orders'] = $pdo->query("
        SELECT COUNT(*) 
        FROM Orders 
        WHERE order_status = 'Новый'
    ")->fetchColumn();

    // 17. Топ-5 избранных товаров
    $stats['top_favorites'] = $pdo->query("
        SELECT p.name, COUNT(f.product_id) as fav_count 
        FROM Favorites f 
        JOIN Products p ON f.product_id = p.product_id 
        GROUP BY p.product_id 
        ORDER BY fav_count DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 18. Сравнения товаров
    $stats['comparisons'] = $pdo->query("
        SELECT COUNT(DISTINCT comparison_id) 
        FROM Comparison_List
    ")->fetchColumn();

    // 19. Корзины с товарами
    $stats['active_carts'] = $pdo->query("
        SELECT COUNT(DISTINCT cart_id) 
        FROM Cart_Items
    ")->fetchColumn();

    // 20. Последние действия
    $stats['recent_logs'] = $pdo->query("
        SELECT action_description, created_at 
        FROM Logs 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 21. Активные промоакции
    $stats['active_promos'] = $pdo->query("
        SELECT title 
        FROM Promotions 
        WHERE status = 'Активна'
    ")->fetchAll(PDO::FETCH_COLUMN);

    // 22. Топ-3 товара по оценкам
    $stats['top_rated'] = $pdo->query("
        SELECT p.name, AVG(r.rating) as avg_rating 
        FROM Reviews r 
        JOIN Products p ON r.product_id = p.product_id 
        GROUP BY p.product_id 
        ORDER BY avg_rating DESC 
        LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 23. Отзывы на модерации
    $stats['pending_reviews'] = $pdo->query("
        SELECT COUNT(*) 
        FROM Reviews 
        WHERE status = 'На модерации'
    ")->fetchColumn();

    // 24. Конверсия заказов
    $stats['conversion'] = $pdo->query("
        SELECT 
            (COUNT(DISTINCT o.order_id) / COUNT(DISTINCT u.user_id)) * 100 
        FROM Users u 
        LEFT JOIN Orders o ON u.user_id = o.user_id
    ")->fetchColumn();

    echo json_encode($stats);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>  