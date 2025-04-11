<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=pk_st;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Authentication check
    $auth_token = $_COOKIE['auth_token'] ?? '';
    if (!$auth_token) {
        throw new Exception('Требуется авторизация', 401);
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
        throw new Exception('Недостаточно прав для доступа', 403);
    }

    $admin_id = $user['user_id'];
    $admin_name = $user['name'];
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $response = [];

    // File upload settings with improved handling
    $root_dir = $_SERVER['DOCUMENT_ROOT'] . '/brow12'; // Explicit path to project
    $upload_base = 'full_image/';
    $products_upload_dir = $upload_base . 'produkts/';
    $news_upload_dir = $upload_base . 'news/';
    $promotions_upload_dir = $upload_base . 'promotions/';

    // Create directories if they don't exist
    foreach ([$products_upload_dir, $news_upload_dir, $promotions_upload_dir] as $dir) {
        $absolute_dir = $root_dir . '/' . $dir;
        if (!file_exists($absolute_dir)) {
            if (!mkdir($absolute_dir, 0777, true)) {
                throw new Exception('Не удалось создать директорию для загрузки изображений: ' . $absolute_dir, 500);
            }
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
                WHERE (u.name LIKE ? OR o.order_code LIKE ? OR o.delivery_address LIKE ?)
                ORDER BY o.created_at DESC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
            $orders = $stmt->fetchAll();

            foreach ($orders as &$order) {
                $stmt = $pdo->prepare("
                    SELECT oi.order_item_id, oi.product_id, oi.quantity, oi.price_per_item, p.name AS product_name, p.image_url
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
                throw new Exception('Не указан ID заказа или статус заказа', 400);
            }

            $stmt = $pdo->query("SHOW COLUMNS FROM Orders LIKE 'order_status'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            preg_match_all("/'([^']+)'/", $row['Type'], $matches);
            $validOrderStatuses = $matches[1];
            if (!in_array($order_status, $validOrderStatuses)) {
                throw new Exception('Недопустимое значение статуса заказа', 400);
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
                throw new Exception('Не указан ID заказа', 400);
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("DELETE FROM Order_Items WHERE order_id = ?");
                $stmt->execute([$order_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Orders WHERE order_id = ?");
                $stmt->execute([$order_id]);
                
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Удалил заказ ID $order_id"]);
                
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Заказ удалён'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_users':
            $search = $_GET['search'] ?? '';
            $stmt = $pdo->prepare("
                SELECT u.user_id, u.name, u.email, u.role_id, r.Name AS role_name, u.created_at, u.status, 
                       u.phone, u.postal_code, u.preferred_payment_method, u.preferred_delivery_method
                FROM Users u
                JOIN Role r ON u.role_id = r.id
                WHERE (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)
                ORDER BY u.created_at DESC
            ");
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
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
                throw new Exception('Не указаны обязательные поля для редактирования пользователя', 400);
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Role WHERE id = ?");
            $stmt->execute([$role_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception('Недопустимая роль', 400);
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
                throw new Exception('Не указан ID пользователя', 400);
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("DELETE FROM User_Sessions WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Удалил пользователя ID $user_id"]);
                
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Пользователь удалён'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_roles':
            $stmt = $pdo->query("SELECT id, Name AS name FROM Role");
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
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

        case 'add_category':
            $name = $_POST['name'] ?? '';
            $parent_category_id = $_POST['parent_category_id'] ?: null;
            if (!$name) {
                throw new Exception('Не указано название категории', 400);
            }
            $stmt = $pdo->prepare("INSERT INTO Categories (name, parent_category_id) VALUES (?, ?)");
            $stmt->execute([$name, $parent_category_id]);
            $category_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Добавил категорию ID $category_id"]);
            $response = ['status' => 'success', 'message' => 'Категория добавлена', 'category_id' => $category_id];
            break;

        case 'edit_category':
            $category_id = $_POST['category_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $parent_category_id = $_POST['parent_category_id'] ?: null;
            if (!$category_id || !$name) {
                throw new Exception('Не указан ID или название категории', 400);
            }
            if ($category_id == $parent_category_id) {
                throw new Exception('Категория не может быть родительской для самой себя', 400);
            }
            $stmt = $pdo->prepare("UPDATE Categories SET name = ?, parent_category_id = ? WHERE category_id = ?");
            $stmt->execute([$name, $parent_category_id, $category_id]);
            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Обновил категорию ID $category_id"]);
            $response = ['status' => 'success', 'message' => 'Категория обновлена'];
            break;

        case 'delete_category':
            $category_id = $_POST['category_id'] ?? 0;
            if (!$category_id) {
                throw new Exception('Не указан ID категории', 400);
            }
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("DELETE FROM Categories WHERE category_id = ?");
                $stmt->execute([$category_id]);
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Удалил категорию ID $category_id"]);
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Категория удалена'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_characteristics':
            $category_id = $_GET['category_id'] ?? null;
            $search = $_GET['search'] ?? '';
            $query = "
                SELECT pc.characteristic_id, pc.name, pc.value_type, c.name AS category_name, c.category_id
                FROM Product_Characteristics pc
                LEFT JOIN Categories c ON pc.category_id = c.category_id
                WHERE pc.name LIKE ?
            ";
            if ($category_id) {
                $query .= " AND pc.category_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute(["%$search%", $category_id]);
            } else {
                $stmt = $pdo->prepare($query);
                $stmt->execute(["%$search%"]);
            }
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'add_characteristic':
            $name = $_POST['name'] ?? '';
            $value_type = $_POST['value_type'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            if (!$name || !$value_type || !$category_id) {
                throw new Exception('Не указаны обязательные поля для характеристики', 400);
            }
            if (!in_array($value_type, ['Текст', 'Число'])) {
                throw new Exception('Недопустимый тип значения', 400);
            }
            $stmt = $pdo->prepare("INSERT INTO Product_Characteristics (category_id, name, value_type) VALUES (?, ?, ?)");
            $stmt->execute([$category_id, $name, $value_type]);
            $characteristic_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Добавил характеристику ID $characteristic_id"]);
            $response = ['status' => 'success', 'message' => 'Характеристика добавлена', 'characteristic_id' => $characteristic_id];
            break;

        case 'edit_characteristic':
            $characteristic_id = $_POST['characteristic_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $value_type = $_POST['value_type'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            if (!$characteristic_id || !$name || !$value_type || !$category_id) {
                throw new Exception('Не указаны обязательные поля для характеристики', 400);
            }
            if (!in_array($value_type, ['Текст', 'Число'])) {
                throw new Exception('Недопустимый тип значения', 400);
            }
            $stmt = $pdo->prepare("UPDATE Product_Characteristics SET name = ?, value_type = ?, category_id = ? WHERE characteristic_id = ?");
            $stmt->execute([$name, $value_type, $category_id, $characteristic_id]);
            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
            $stmt->execute([$admin_id, "Обновил характеристику ID $characteristic_id"]);
            $response = ['status' => 'success', 'message' => 'Характеристика обновлена'];
            break;

        case 'delete_characteristic':
            $characteristic_id = $_POST['characteristic_id'] ?? 0;
            if (!$characteristic_id) {
                throw new Exception('Не указан ID характеристики', 400);
            }
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("DELETE FROM Product_Characteristic_Values WHERE characteristic_id = ?");
                $stmt->execute([$characteristic_id]);
                $stmt = $pdo->prepare("DELETE FROM Product_Characteristics WHERE characteristic_id = ?");
                $stmt->execute([$characteristic_id]);
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Удалил характеристику ID $characteristic_id"]);
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Характеристика удалена'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_products':
            $search = $_GET['search'] ?? '';
            $category_id = $_GET['category_id'] ?? null;
            
            $query = "
                SELECT p.product_id, p.category_id, p.name, p.description, p.price, p.stock_quantity, p.image_url, 
                       p.is_bestseller, p.is_new, p.discount, p.created_at, c.name AS category_name
                FROM Products p
                LEFT JOIN Categories c ON p.category_id = c.category_id
                WHERE (p.name LIKE ? OR p.description LIKE ?)
            ";
            
            $params = ["%$search%", "%$search%"];
            
            if ($category_id) {
                $query .= " AND p.category_id = ?";
                $params[] = $category_id;
            }
            
            $query .= " ORDER BY p.created_at DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
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
                throw new Exception('Необходимо указать название, цену и количество на складе', 400);
            }

            $image_url = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension'] ?? '');
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowed_types)) {
                    throw new Exception('Недопустимый тип файла. Разрешены только JPG, PNG, GIF и WEBP.', 400);
                }
                
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Файл слишком большой. Максимальный размер - 5MB.', 400);
                }
                
                $image_name = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_info['filename']) . '.' . $extension;
                $image_path = $root_dir . '/' . $products_upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_url = $products_upload_dir . $image_name;
                } else {
                    throw new Exception('Не удалось загрузить изображение. Ошибка: ' . ($_FILES['image']['error'] ?? 'неизвестная ошибка'), 500);
                }
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO Products (category_id, name, description, price, stock_quantity, image_url, is_bestseller, is_new, discount, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$category_id, $name, $description, $price, $stock_quantity, $image_url, $is_bestseller, $is_new, $discount]);
                $product_id = $pdo->lastInsertId();

                if ($category_id) {
                    $stmt = $pdo->prepare("SELECT characteristic_id, name, value_type FROM Product_Characteristics WHERE category_id = ?");
                    $stmt->execute([$category_id]);
                    $characteristics = $stmt->fetchAll();

                    foreach ($characteristics as $char) {
                        $char_id = $char['characteristic_id'];
                        $char_value = $_POST["characteristic_$char_id"] ?? '';
                        if ($char_value) {
                            if ($char['value_type'] === 'Число' && !is_numeric($char_value)) {
                                throw new Exception("Значение характеристики '{$char['name']}' должно быть числом", 400);
                            }
                            $stmt = $pdo->prepare("INSERT INTO Product_Characteristic_Values (product_id, characteristic_id, value) VALUES (?, ?, ?)");
                            $stmt->execute([$product_id, $char_id, $char_value]);
                        }
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Добавил продукт ID $product_id"]);

                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Продукт добавлен', 'product_id' => $product_id];
            } catch (Exception $e) {
                if ($image_url && file_exists($root_dir . '/' . $image_url)) {
                    unlink($root_dir . '/' . $image_url);
                }
                $pdo->rollBack();
                throw $e;
            }
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
                throw new Exception('Не указаны обязательные поля для редактирования продукта', 400);
            }

            $stmt = $pdo->prepare("SELECT image_url FROM Products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $current_image = $stmt->fetchColumn();

            $image_url = $current_image;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension'] ?? '');
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowed_types)) {
                    throw new Exception('Недопустимый тип файла. Разрешены только JPG, PNG, GIF и WEBP.', 400);
                }
                
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Файл слишком большой. Максимальный размер - 5MB.', 400);
                }
                
                $image_name = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_info['filename']) . '.' . $extension;
                $image_path = $root_dir . '/' . $products_upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_url = $products_upload_dir . $image_name;
                    if ($current_image && file_exists($root_dir . '/' . $current_image) && !str_contains($current_image, 'default.')) {
                        unlink($root_dir . '/' . $current_image);
                    }
                } else {
                    throw new Exception('Не удалось загрузить изображение. Ошибка: ' . ($_FILES['image']['error'] ?? 'неизвестная ошибка'), 500);
                }
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    UPDATE Products 
                    SET name = ?, description = ?, price = ?, stock_quantity = ?, is_bestseller = ?, is_new = ?, 
                        discount = ?, category_id = ?, image_url = ?
                    WHERE product_id = ?
                ");
                $stmt->execute([$name, $description, $price, $stock_quantity, $is_bestseller, $is_new, $discount, $category_id, $image_url, $product_id]);

                $stmt = $pdo->prepare("DELETE FROM Product_Characteristic_Values WHERE product_id = ?");
                $stmt->execute([$product_id]);

                if ($category_id) {
                    $stmt = $pdo->prepare("SELECT characteristic_id, name, value_type FROM Product_Characteristics WHERE category_id = ?");
                    $stmt->execute([$category_id]);
                    $characteristics = $stmt->fetchAll();

                    foreach ($characteristics as $char) {
                        $char_id = $char['characteristic_id'];
                        $char_value = $_POST["characteristic_$char_id"] ?? '';
                        if ($char_value) {
                            if ($char['value_type'] === 'Число' && !is_numeric($char_value)) {
                                throw new Exception("Значение характеристики '{$char['name']}' должно быть числом", 400);
                            }
                            $stmt = $pdo->prepare("INSERT INTO Product_Characteristic_Values (product_id, characteristic_id, value) VALUES (?, ?, ?)");
                            $stmt->execute([$product_id, $char_id, $char_value]);
                        }
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Обновил продукт ID $product_id"]);

                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Продукт обновлён'];
            } catch (Exception $e) {
                if ($image_url !== $current_image && $image_url && file_exists($root_dir . '/' . $image_url)) {
                    unlink($root_dir . '/' . $image_url);
                }
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'delete_product':
            $product_id = $_POST['product_id'] ?? 0;
            if (!$product_id) {
                throw new Exception('Не указан ID продукта', 400);
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("SELECT image_url FROM Products WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $image_url = $stmt->fetchColumn();

                $stmt = $pdo->prepare("DELETE FROM Order_Items WHERE product_id = ?");
                $stmt->execute([$product_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Product_Characteristic_Values WHERE product_id = ?");
                $stmt->execute([$product_id]);
                
                $stmt = $pdo->prepare("DELETE FROM Products WHERE product_id = ?");
                $stmt->execute([$product_id]);
                
                if ($image_url && file_exists($root_dir . '/' . $image_url) && !str_contains($image_url, 'default.')) {
                    unlink($root_dir . '/' . $image_url);
                }
                
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Удалил продукт ID $product_id"]);
                
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Продукт удалён'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_news':
            $search = $_GET['search'] ?? '';
            $stmt = $pdo->prepare("
                SELECT news_id, title, content, image_url, created_at
                FROM News
                WHERE title LIKE ?
                ORDER BY created_at DESC
            ");
            $stmt->execute(["%$search%"]);
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'add_news':
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            if (!$title || !$content) {
                throw new Exception('Не указаны заголовок или содержание новости', 400);
            }
            $image_url = 'ajax/uploads/news_default.jpg'; // Default image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension'] ?? '');
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowed_types)) {
                    throw new Exception('Недопустимый тип файла. Разрешены только JPG, PNG, GIF и WEBP.', 400);
                }
                
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Файл слишком большой. Максимальный размер - 5MB.', 400);
                }
                
                $image_name = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_info['filename']) . '.' . $extension;
                $image_path = $root_dir . '/' . $news_upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_url = $news_upload_dir . $image_name;
                } else {
                    throw new Exception('Не удалось загрузить изображение', 500);
                }
            }
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO News (title, content, image_url, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$title, $content, $image_url]);
                $news_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Добавил новость ID $news_id"]);
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Новость добавлена', 'news_id' => $news_id];
            } catch (Exception $e) {
                if ($image_url !== 'ajax/uploads/news_default.jpg' && file_exists($root_dir . '/' . $image_url)) {
                    unlink($root_dir . '/' . $image_url);
                }
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'edit_news':
            $news_id = $_POST['news_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            if (!$news_id || !$title || !$content) {
                throw new Exception('Не указаны обязательные поля для новости', 400);
            }
            $stmt = $pdo->prepare("SELECT image_url FROM News WHERE news_id = ?");
            $stmt->execute([$news_id]);
            $current_image = $stmt->fetchColumn();

            $image_url = $current_image;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension'] ?? '');
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowed_types)) {
                    throw new Exception('Недопустимый тип файла. Разрешены только JPG, PNG, GIF и WEBP.', 400);
                }
                
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Файл слишком большой. Максимальный размер - 5MB.', 400);
                }
                
                $image_name = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_info['filename']) . '.' . $extension;
                $image_path = $root_dir . '/' . $news_upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_url = $news_upload_dir . $image_name;
                    if ($current_image && file_exists($root_dir . '/' . $current_image) && !str_contains($current_image, 'news_default.')) {
                        unlink($root_dir . '/' . $current_image);
                    }
                } else {
                    throw new Exception('Не удалось загрузить изображение', 500);
                }
            }
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE News SET title = ?, content = ?, image_url = ? WHERE news_id = ?");
                $stmt->execute([$title, $content, $image_url, $news_id]);
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Обновил новость ID $news_id"]);
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Новость обновлена'];
            } catch (Exception $e) {
                if ($image_url !== $current_image && $image_url && file_exists($root_dir . '/' . $image_url)) {
                    unlink($root_dir . '/' . $image_url);
                }
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'delete_news':
            $news_id = $_POST['news_id'] ?? 0;
            if (!$news_id) {
                throw new Exception('Не указан ID новости', 400);
            }
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("SELECT image_url FROM News WHERE news_id = ?");
                $stmt->execute([$news_id]);
                $image_url = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("DELETE FROM News WHERE news_id = ?");
                $stmt->execute([$news_id]);
                
                if ($image_url && file_exists($root_dir . '/' . $image_url) && !str_contains($image_url, 'news_default.')) {
                    unlink($root_dir . '/' . $image_url);
                }
                
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Удалил новость ID $news_id"]);
                
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Новость удалена'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_promotions':
            $search = $_GET['search'] ?? '';
            $stmt = $pdo->prepare("
                SELECT p.promotion_id, p.title, p.description, p.image_url, p.start_date, p.end_date, p.status, p.category_id, c.name AS category_name
                FROM Promotions p
                LEFT JOIN Categories c ON p.category_id = c.category_id
                WHERE p.title LIKE ?
                ORDER BY p.start_date DESC
            ");
            $stmt->execute(["%$search%"]);
            $response = ['status' => 'success', 'data' => $stmt->fetchAll()];
            break;

        case 'add_promotion':
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $category_id = $_POST['category_id'] ?: null;
            if (!$title || !$start_date || !$end_date) {
                throw new Exception('Не указаны обязательные поля для акции', 400);
            }
            $image_url = 'images/promo-default.jpg'; // Default image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension'] ?? '');
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowed_types)) {
                    throw new Exception('Недопустимый тип файла. Разрешены только JPG, PNG, GIF и WEBP.', 400);
                }
                
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Файл слишком большой. Максимальный размер - 5MB.', 400);
                }
                
                $image_name = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_info['filename']) . '.' . $extension;
                $image_path = $root_dir . '/' . $promotions_upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_url = $promotions_upload_dir . $image_name;
                } else {
                    throw new Exception('Не удалось загрузить изображение', 500);
                }
            }
            $status = (strtotime($end_date) < time()) ? 'Завершена' : 'Активна';
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO Promotions (title, description, image_url, start_date, end_date, category_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $image_url, $start_date, $end_date, $category_id, $status]);
                $promotion_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Добавил акцию ID $promotion_id"]);
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Акция добавлена', 'promotion_id' => $promotion_id];
            } catch (Exception $e) {
                if ($image_url !== 'images/promo-default.jpg' && file_exists($root_dir . '/' . $image_url)) {
                    unlink($root_dir . '/' . $image_url);
                }
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'edit_promotion':
            $promotion_id = $_POST['promotion_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $category_id = $_POST['category_id'] ?: null;
            if (!$promotion_id || !$title || !$start_date || !$end_date) {
                throw new Exception('Не указаны обязательные поля для акции', 400);
            }
            $stmt = $pdo->prepare("SELECT image_url FROM Promotions WHERE promotion_id = ?");
            $stmt->execute([$promotion_id]);
            $current_image = $stmt->fetchColumn();

            $image_url = $current_image;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension'] ?? '');
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowed_types)) {
                    throw new Exception('Недопустимый тип файла. Разрешены только JPG, PNG, GIF и WEBP.', 400);
                }
                
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Файл слишком большой. Максимальный размер - 5MB.', 400);
                }
                
                $image_name = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_info['filename']) . '.' . $extension;
                $image_path = $root_dir . '/' . $promotions_upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $image_url = $promotions_upload_dir . $image_name;
                    if ($current_image && file_exists($root_dir . '/' . $current_image) && !str_contains($current_image, 'promo-default.')) {
                        unlink($root_dir . '/' . $current_image);
                    }
                } else {
                    throw new Exception('Не удалось загрузить изображение', 500);
                }
            }
            $status = (strtotime($end_date) < time()) ? 'Завершена' : 'Активна';
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE Promotions SET title = ?, description = ?, image_url = ?, start_date = ?, end_date = ?, category_id = ?, status = ? WHERE promotion_id = ?");
                $stmt->execute([$title, $description, $image_url, $start_date, $end_date, $category_id, $status, $promotion_id]);
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Обновил акцию ID $promotion_id"]);
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Акция обновлена'];
            } catch (Exception $e) {
                if ($image_url !== $current_image && $image_url && file_exists($root_dir . '/' . $image_url)) {
                    unlink($root_dir . '/' . $image_url);
                }
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'delete_promotion':
            $promotion_id = $_POST['promotion_id'] ?? 0;
            if (!$promotion_id) {
                throw new Exception('Не указан ID акции', 400);
            }
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("SELECT image_url FROM Promotions WHERE promotion_id = ?");
                $stmt->execute([$promotion_id]);
                $image_url = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("DELETE FROM Promotions WHERE promotion_id = ?");
                $stmt->execute([$promotion_id]);
                
                if ($image_url && file_exists($root_dir . '/' . $image_url) && !str_contains($image_url, 'promo-default.')) {
                    unlink($root_dir . '/' . $image_url);
                }
                
                $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action) VALUES (?, ?)");
                $stmt->execute([$admin_id, "Удалил акцию ID $promotion_id"]);
                
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Акция удалена'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_product_characteristics':
            $product_id = $_GET['product_id'] ?? 0;
            if (!$product_id) {
                throw new Exception('Не указан ID продукта', 400);
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
            $response = ['status' => 'success', 'data' => [
                'admin_name' => $admin_name,
                'admin_id' => $admin_id,
                'upload_dir' => $root_dir . '/' . $products_upload_dir // For debugging
            ]];
            break;

        default:
            throw new Exception('Неизвестное действие: ' . $action, 400);
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>