<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=pk_st;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $auth_token = $_COOKIE['auth_token'] ?? '';
    if (!$auth_token) {
        throw new Exception('Требуется авторизация');
    }

    $stmt = $pdo->prepare("
        SELECT u.user_id, u.role_id, u.name
        FROM User_Sessions s
        JOIN Users u ON s.user_id = u.user_id
        WHERE s.token = ? AND s.expires_at > NOW()
    ");
    $stmt->execute([$auth_token]);
    $user = $stmt->fetch();

    if (!$user || !in_array($user['role_id'], [1, 3, 4])) {
        throw new Exception('Недостаточно прав для доступа');
    }

    $admin_id = $user['user_id'];
    $admin_name = $user['name'];
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $response = [];

    // Настройки загрузки изображений
    $root_dir = $_SERVER['DOCUMENT_ROOT'];
    $upload_dir = '/brow12/full_image/produkts/';
    $absolute_upload_dir = $root_dir . $upload_dir;
    
    // Создаем директорию, если ее нет
    if (!file_exists($absolute_upload_dir)) {
        if (!mkdir($absolute_upload_dir, 0777, true)) {
            throw new Exception('Не удалось создать директорию для загрузки изображений');
        }
    }

    switch ($action) {
        case 'get_analytics':
            $total_users = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
            $total_orders = $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
            $total_products = $pdo->query("SELECT COUNT(*) FROM Products")->fetchColumn();
            $total_sales = $pdo->query("SELECT SUM(total_price) FROM Orders WHERE order_status = 'Доставлен'")->fetchColumn();

            $response = [
                'status' => 'success',
                'data' => [
                    'total_users' => $total_users,
                    'total_orders' => $total_orders,
                    'total_products' => $total_products,
                    'total_sales' => $total_sales ?: 0
                ]
            ];
            break;

        case 'get_orders':
            $search = $_GET['search'] ?? '';
            $query = "
                SELECT o.order_id, o.order_code, o.user_id, o.order_status, o.delivery_address, o.total_price, o.created_at, 
                       u.name AS user_name, u.email AS user_email, u.phone AS user_phone, u.postal_code, 
                       u.preferred_payment_method, u.preferred_delivery_method
                FROM Orders o
                LEFT JOIN Users u ON o.user_id = u.user_id
                WHERE (u.name LIKE ? OR o.delivery_address LIKE ?)
                ORDER BY o.created_at DESC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute(["%$search%", "%$search%"]);
            $orders = $stmt->fetchAll();

            foreach ($orders as &$order) {
                $stmt = $pdo->prepare("
                    SELECT oi.order_item_id, oi.product_id, oi.quantity, oi.price_per_item, p.name AS product_name
                    FROM Order_Items oi
                    JOIN Products p ON oi.product_id = p.product_id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$order['order_id']]);
                $order['items'] = $stmt->fetchAll();
            }

            $response = ['status' => 'success', 'data' => $orders];
            break;

        case 'edit_order':
            $order_id = $_POST['order_id'] ?? 0;
            $order_status = $_POST['order_status'] ?? '';
            $delivery_address = $_POST['delivery_address'] ?? '';

            if (!$order_id || !$order_status) {
                throw new Exception('Не указан ID заказа или статус заказа');
            }

            $stmt = $pdo->query("SHOW COLUMNS FROM Orders LIKE 'order_status'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            preg_match_all("/'([^']+)'/", $row['Type'], $matches);
            $validOrderStatuses = $matches[1];
            if (!in_array($order_status, $validOrderStatuses)) {
                throw new Exception('Недопустимое значение статуса заказа');
            }

            $stmt = $pdo->prepare("UPDATE Orders SET order_status = ?, delivery_address = ? WHERE order_id = ?");
            $stmt->execute([$order_status, $delivery_address, $order_id]);

            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Обновил заказ ID $order_id"]);

            $response = ['status' => 'success', 'message' => 'Заказ обновлён'];
            break;

        case 'delete_order':
            $order_id = $_POST['order_id'] ?? 0;
            if (!$order_id) {
                throw new Exception('Не указан ID заказа');
            }

            $stmt = $pdo->prepare("DELETE FROM Orders WHERE order_id = ?");
            $stmt->execute([$order_id]);

            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Удалил заказ ID $order_id"]);

            $response = ['status' => 'success', 'message' => 'Заказ удалён'];
            break;

        // Остальные методы остаются без изменений
        case 'get_users':
            $search = $_GET['search'] ?? '';
            $stmt = $pdo->prepare("
                SELECT u.user_id, u.name, u.email, u.role_id, r.Name AS role_name, u.created_at, u.status, 
                       u.phone, u.postal_code, u.preferred_payment_method, u.preferred_delivery_method
                FROM Users u
                JOIN Role r ON u.role_id = r.id
                WHERE (u.name LIKE ? OR u.email LIKE ?)
                ORDER BY u.created_at DESC
            ");
            $stmt->execute(["%$search%", "%$search%"]);
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'edit_user':
            $user_id = $_POST['user_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $role_id = $_POST['role_id'] ?? 0;
            $phone = $_POST['phone'] ?? null;
            $postal_code = $_POST['postal_code'] ?? null;
            $preferred_payment_method = $_POST['preferred_payment_method'] ?? null;
            $preferred_delivery_method = $_POST['preferred_delivery_method'] ?? null;

            if (!$user_id || !$name || !$email || !$role_id) {
                throw new Exception('Не указаны обязательные поля для редактирования пользователя');
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Role WHERE id = ?");
            $stmt->execute([$role_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception('Недопустимая роль');
            }

            $stmt = $pdo->prepare("
                UPDATE Users 
                SET name = ?, email = ?, role_id = ?, phone = ?, postal_code = ?, 
                    preferred_payment_method = ?, preferred_delivery_method = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$name, $email, $role_id, $phone, $postal_code, 
                           $preferred_payment_method, $preferred_delivery_method, $user_id]);

            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Обновил пользователя ID $user_id"]);

            $response = ['status' => 'success', 'message' => 'Пользователь обновлён'];
            break;

        case 'delete_user':
            $user_id = $_POST['user_id'] ?? 0;
            if (!$user_id) {
                throw new Exception('Не указан ID пользователя');
            }

            $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Удалил пользователя ID $user_id"]);

            $response = ['status' => 'success', 'message' => 'Пользователь удалён'];
            break;

        case 'get_roles':
            $stmt = $pdo->query("SELECT id, Name AS name FROM Role");
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'get_products':
            $search = $_GET['search'] ?? '';
            $stmt = $pdo->prepare("
                SELECT p.product_id, p.category_id, p.name, p.description, p.price, p.stock_quantity, p.image_url, 
                       p.is_bestseller, p.is_new, p.discount, p.created_at, c.name AS category_name
                FROM Products p
                LEFT JOIN Categories c ON p.category_id = c.category_id
                WHERE (p.name LIKE ? OR p.description LIKE ?)
                ORDER BY p.created_at DESC
            ");
            $stmt->execute(["%$search%", "%$search%"]);
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'add_product':
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $stock_quantity = $_POST['stock_quantity'] ?? 0;
            $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
            $is_new = isset($_POST['is_new']) ? 1 : 0;
            $discount = $_POST['discount'] ?? 0;
            $category_id = $_POST['category_id'] ?? null;

            if (!$name || !$price || !$stock_quantity) {
                throw new Exception('Необходимо указать название, цену и количество на складе');
            }

            $image_url = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = uniqid() . '-' . basename($_FILES['image']['name']);
                $image_path = $absolute_upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_url = $upload_dir . $image_name;
                } else {
                    throw new Exception('Не удалось загрузить изображение');
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO Products (category_id, name, description, price, stock_quantity, image_url, is_bestseller, is_new, discount, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$category_id, $name, $description, $price, $stock_quantity, $image_url, $is_bestseller, $is_new, $discount]);
            $product_id = $pdo->lastInsertId();

            // Сохранение характеристик
            $stmt = $pdo->prepare("SELECT characteristic_id, name, value_type FROM Product_Characteristics WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $characteristics = $stmt->fetchAll();

            foreach ($characteristics as $char) {
                $char_id = $char['characteristic_id'];
                $char_value = $_POST["characteristic_$char_id"] ?? '';
                if ($char_value) {
                    if ($char['value_type'] === 'Число' && !is_numeric($char_value)) {
                        throw new Exception("Значение характеристики '{$char['name']}' должно быть числом");
                    }
                    $stmt = $pdo->prepare("INSERT INTO Product_Characteristic_Values (product_id, characteristic_id, value) VALUES (?, ?, ?)");
                    $stmt->execute([$product_id, $char_id, $char_value]);
                }
            }

            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Добавил продукт ID $product_id"]);

            $response = ['status' => 'success', 'message' => 'Продукт добавлен'];
            break;

        case 'edit_product':
            $product_id = $_POST['product_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $stock_quantity = $_POST['stock_quantity'] ?? 0;
            $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
            $is_new = isset($_POST['is_new']) ? 1 : 0;
            $discount = $_POST['discount'] ?? 0;
            $category_id = $_POST['category_id'] ?? null;

            if (!$product_id || !$name || !$price || !$stock_quantity) {
                throw new Exception('Не указаны обязательные поля для редактирования продукта');
            }

            $stmt = $pdo->prepare("SELECT image_url FROM Products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $current_image = $stmt->fetchColumn();

            $image_url = $current_image;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = uniqid() . '-' . basename($_FILES['image']['name']);
                $image_path = $absolute_upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_url = $upload_dir . $image_name;
                    // Удаляем старое изображение, если оно существует и не является дефолтным
                    if ($current_image && file_exists($root_dir . $current_image) && !str_contains($current_image, 'default.jpg')) {
                        unlink($root_dir . $current_image);
                    }
                } else {
                    throw new Exception('Не удалось загрузить изображение');
                }
            }

            $stmt = $pdo->prepare("
                UPDATE Products 
                SET name = ?, description = ?, price = ?, stock_quantity = ?, is_bestseller = ?, is_new = ?, discount = ?, category_id = ?, image_url = ?
                WHERE product_id = ?
            ");
            $stmt->execute([$name, $description, $price, $stock_quantity, $is_bestseller, $is_new, $discount, $category_id, $image_url, $product_id]);

            // Обновление характеристик
            $stmt = $pdo->prepare("DELETE FROM Product_Characteristic_Values WHERE product_id = ?");
            $stmt->execute([$product_id]);

            $stmt = $pdo->prepare("SELECT characteristic_id, name, value_type FROM Product_Characteristics WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $characteristics = $stmt->fetchAll();

            foreach ($characteristics as $char) {
                $char_id = $char['characteristic_id'];
                $char_value = $_POST["characteristic_$char_id"] ?? '';
                if ($char_value) {
                    if ($char['value_type'] === 'Число' && !is_numeric($char_value)) {
                        throw new Exception("Значение характеристики '{$char['name']}' должно быть числом");
                    }
                    $stmt = $pdo->prepare("INSERT INTO Product_Characteristic_Values (product_id, characteristic_id, value) VALUES (?, ?, ?)");
                    $stmt->execute([$product_id, $char_id, $char_value]);
                }
            }

            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Обновил продукт ID $product_id"]);

            $response = ['status' => 'success', 'message' => 'Продукт обновлён'];
            break;

        case 'delete_product':
            $product_id = $_POST['product_id'] ?? 0;
            if (!$product_id) {
                throw new Exception('Не указан ID продукта');
            }

            $stmt = $pdo->prepare("SELECT image_url FROM Products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $image_url = $stmt->fetchColumn();
            if ($image_url && file_exists($root_dir . $image_url) && !str_contains($image_url, 'default.jpg')) {
                unlink($root_dir . $image_url);
            }

            $stmt = $pdo->prepare("DELETE FROM Products WHERE product_id = ?");
            $stmt->execute([$product_id]);

            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Удалил продукт ID $product_id"]);

            $response = ['status' => 'success', 'message' => 'Продукт удалён'];
            break;

        case 'get_categories':
            $search = $_GET['search'] ?? '';
            $stmt = $pdo->prepare("
                SELECT c.category_id, c.parent_category_id, c.name, pc.name AS parent_name
                FROM Categories c
                LEFT JOIN Categories pc ON c.parent_category_id = pc.category_id
                WHERE c.name LIKE ?
                ORDER BY c.category_id
            ");
            $stmt->execute(["%$search%"]);
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'get_characteristics':
            $category_id = $_GET['category_id'] ?? 0;
            if (!$category_id) {
                throw new Exception('Не указан ID категории');
            }
            $stmt = $pdo->prepare("SELECT characteristic_id, name, value_type FROM Product_Characteristics WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'get_product_characteristics':
            $product_id = $_GET['product_id'] ?? 0;
            if (!$product_id) {
                throw new Exception('Не указан ID продукта');
            }
            $stmt = $pdo->prepare("
                SELECT pcv.characteristic_id, pcv.value, pc.name, pc.value_type 
                FROM Product_Characteristic_Values pcv
                JOIN Product_Characteristics pc ON pcv.characteristic_id = pc.characteristic_id
                WHERE pcv.product_id = ?
            ");
            $stmt->execute([$product_id]);
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'get_admin_info':
            $response = ['status' => 'success', 'data' => ['admin_name' => $admin_name]];
            break;

        default:
            throw new Exception('Неизвестное действие: ' . $action);
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>