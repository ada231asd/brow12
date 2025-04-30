<?php
function handleAnalytics($pdo, $table, $role_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        die(json_encode(['error' => 'Метод не поддерживается']));
    }

    $hasPermission = checkRolePermission($role_id, "get_$table");
    if (!$hasPermission) {
        die(json_encode(['error' => "Недостаточно прав для get_$table, role_id: $role_id"]));
    }

    try {
        $mode = $_GET['mode'] ?? '';
        if ($mode === 'analytics') {
            switch ($table) {
                case 'Orders':
                    $stmt = $pdo->query("SELECT order_status, COUNT(*) as count FROM Orders GROUP BY order_status");
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($result)) {
                        echo json_encode(['message' => 'Нет данных в таблице Orders']);
                    } else {
                        echo json_encode($result);
                    }
                    break;
                case 'Products':
                    $stmt = $pdo->query("SELECT p.name, p.stock_quantity, COUNT(oi.order_id) as orders FROM Products p LEFT JOIN Order_Items oi ON p.product_id = oi.product_id GROUP BY p.product_id");
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($result)) {
                        echo json_encode(['message' => 'Нет данных в таблице Products']);
                    } else {
                        echo json_encode($result);
                    }
                    break;
                case 'Users':
                    $stmt = $pdo->query("SELECT r.Name as role, COUNT(u.user_id) as count FROM Users u JOIN Role r ON u.role_id = r.id GROUP BY r.id");
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($result)) {
                        echo json_encode(['message' => 'Нет данных в таблице Users']);
                    } else {
                        echo json_encode($result);
                    }
                    break;
                default:
                    echo json_encode(['error' => 'Аналитика для этой таблицы не поддерживается']);
            }
        } else {
            switch ($table) {
                case 'Reviews':
                    $stmt = $pdo->query("
                        SELECT r.review_id AS _id, p.name AS product_name, u.email AS user_email, r.rating, r.comment, r.status
                        FROM Reviews r
                        JOIN Products p ON r.product_id = p.product_id
                        JOIN Users u ON r.user_id = u.user_id
                    ");
                    break;
                case 'Users':
                    $stmt = $pdo->query("
                        SELECT u.user_id AS _id, u.name, u.email, r.Name as role_name, u.status
                        FROM Users u
                        JOIN Role r ON u.role_id = r.id
                    ");
                    break;
                case 'Orders':
                    $stmt = $pdo->query("
                        SELECT o.order_id AS _id, o.order_code, u.email AS user_email, o.order_status, o.delivery_address, o.total_price, o.created_at
                        FROM Orders o
                        JOIN Users u ON o.user_id = u.user_id
                    ");
                    break;
                case 'Products':
                    $stmt = $pdo->query("
                        SELECT product_id AS _id, name, description, price, stock_quantity, is_bestseller, is_new, discount, image_url
                        FROM Products
                    ");
                    break;
                case 'Role':
                    $stmt = $pdo->query("SELECT id AS _id, Name FROM Role");
                    break;
                case 'News':
                    $stmt = $pdo->query("
                        SELECT news_id AS _id, title, content, image_url, created_at
                        FROM News
                    ");
                    break;
                case 'Promotions':
                    $stmt = $pdo->query("
                        SELECT promotion_id AS _id, title, description, image_url, start_date, end_date, status
                        FROM Promotions
                    ");
                    break;
                default:
                    $keyFields = [
                        'Categories' => ['category_id AS _id', 'name'],
                        'News' => ['news_id AS _id', 'title', 'content', 'image_url', 'created_at'],
                        'Promotions' => ['promotion_id AS _id', 'title', 'description', 'image_url', 'start_date', 'end_date', 'status'],
                        'Cart' => ['cart_id AS _id', 'user_id'],
                        'Cart_Items' => ['cart_item_id AS _id', 'cart_id', 'product_id', 'quantity'],
                        'Admin_Logs' => ['log_id AS _id', 'admin_id', 'action', 'created_at', 'table_name', 'record_id', 'action_type'],
                        'Comparison_Items' => ['comparison_item_id AS _id', 'comparison_id', 'product_id'],
                        'Comparison_List' => ['comparison_id AS _id', 'user_id'],
                        'Delivery_Status' => ['delivery_status_id AS _id', 'order_id', 'status', 'updated_at'],
                        'Favorites' => ['favorite_id AS _id', 'user_id', 'product_id'],
                        'Feedback' => ['feedback_id AS _id', 'category', 'user_id', 'message', 'status', 'created_at'],
                        'Logs' => ['log_id AS _id', 'user_id', 'action_description', 'created_at'],
                        'Product_Characteristics' => ['characteristic_id AS _id', 'category_id', 'name', 'value_type'],
                        'Product_Characteristic_Values' => ['product_id', 'characteristic_id', 'value'],
                        'Recommendations' => ['recommendation_id AS _id', 'user_id', 'product_id'],
                        'Stores' => ['store_id AS _id', 'name', 'address', 'phone', 'working_hours', 'status'],
                        'User_Sessions' => ['session_id AS _id', 'user_id', 'created_at', 'expires_at']
                    ];
                    if (isset($keyFields[$table])) {
                        $fields = implode(', ', $keyFields[$table]);
                        $stmt = $pdo->query("SELECT $fields FROM $table");
                    } else {
                        $stmt = $pdo->query("SELECT * FROM $table");
                    }
            }
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($result)) {
                echo json_encode(['message' => "Нет данных в таблице $table"]);
            } else {
                echo json_encode($result);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Ошибка аналитики: ' . $e->getMessage()]);
    }
}