<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../auth.php';

if (!isLoggedIn()) {
    die(json_encode(['error' => 'Доступ запрещен: не авторизован']));
}

$userData = getUserData();
$role_id = $userData['role_id'];

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? null;
$roleId = $_GET['role_id'] ?? null;
$categoryId = $_GET['category_id'] ?? null;

switch ($action) {
    case 'create':
        require_once 'create.php';
        handleCreate($pdo, $table, $role_id);
        break;
    case 'update':
        require_once 'update.php';
        handleUpdate($pdo, $table, $role_id);
        break;
    case 'delete':
        require_once 'delete.php';
        handleDelete($pdo, $table, $role_id);
        break;
    case 'moderate_review':
        if ($method !== 'POST') {
            die(json_encode(['error' => 'Метод не поддерживается']));
        }
        if (!checkRolePermission($role_id, 'edit_reviews')) {
            die(json_encode(['error' => 'Недостаточно прав']));
        }
        $reviewId = $_GET['review_id'] ?? null;
        $status = $_GET['status'] ?? null;
        if (!$reviewId || !$status) {
            die(json_encode(['error' => 'Не указаны параметры']));
        }
        $stmt = $pdo->prepare("UPDATE Reviews SET status = ? WHERE review_id = ?");
        $stmt->execute([$status, $reviewId]);
        echo json_encode(['success' => true]);
        break;
    case 'update_permissions':
        if ($method !== 'POST') {
            die(json_encode(['error' => 'Метод не поддерживается']));
        }
        if (!checkRolePermission($role_id, 'edit_role')) {
            die(json_encode(['error' => 'Недостаточно прав']));
        }
        $roleId = $_GET['role_id'] ?? null;
        if (!$roleId) {
            die(json_encode(['error' => 'Не указан role_id']));
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $permissions = $input['permissions'] ?? [];

        $stmt = $pdo->prepare("DELETE FROM Role_Permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);

        foreach ($permissions as $perm) {
            $stmt = $pdo->prepare("INSERT INTO Role_Permissions (role_id, permission) VALUES (?, ?)");
            $stmt->execute([$roleId, $perm]);
        }
        echo json_encode(['success' => true]);
        break;
    case 'update_characteristic_value':
        if ($method !== 'POST') {
            die(json_encode(['error' => 'Метод не поддерживается']));
        }
        if (!checkRolePermission($role_id, 'edit_characteristic')) {
            die(json_encode(['error' => 'Недостаточно прав']));
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $product_id = $input['product_id'] ?? null;
        $characteristic_id = $input['characteristic_id'] ?? null;
        $value = $input['value'] ?? null;

        if (!$product_id || !$characteristic_id || !$value) {
            die(json_encode(['error' => 'Не указаны все параметры']));
        }

        $stmt = $pdo->prepare("
            INSERT INTO Product_Characteristic_Values (product_id, characteristic_id, value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE value = ?
        ");
        $stmt->execute([$product_id, $characteristic_id, $value, $value]);
        echo json_encode(['success' => true]);
        break;
    case 'analytics':
        require_once 'analytics.php';
        if ($id) {
            if (!checkRolePermission($role_id, "get_$table")) {
                die(json_encode(['error' => "Недостаточно прав для get_$table"]));
            }
            switch ($table) {
                case 'Reviews':
                    $stmt = $pdo->prepare("
                        SELECT r.review_id AS _id, p.name AS product_name, u.email AS user_email, r.rating, r.comment, r.status
                        FROM Reviews r
                        JOIN Products p ON r.product_id = p.product_id
                        JOIN Users u ON r.user_id = u.user_id
                        WHERE r.review_id = ?
                    ");
                    $stmt->execute([$id]);
                    break;
                case 'Users':
                    $stmt = $pdo->prepare("
                        SELECT u.user_id AS _id, u.name, u.email, r.Name as role_name, u.status, u.role_id
                        FROM Users u
                        JOIN Role r ON u.role_id = r.id
                        WHERE u.user_id = ?
                    ");
                    $stmt->execute([$id]);
                    break;
                case 'Orders':
                    $stmt = $pdo->prepare("
                        SELECT o.order_id AS _id, o.order_code, u.email AS user_email, o.order_status, o.delivery_address, o.total_price, o.created_at
                        FROM Orders o
                        JOIN Users u ON o.user_id = u.user_id
                        WHERE o.order_id = ?
                    ");
                    $stmt->execute([$id]);
                    break;
                case 'Products':
                    $stmt = $pdo->prepare("
                        SELECT product_id AS _id, name, description, price, stock_quantity, is_bestseller, is_new, discount, image_url
                        FROM Products
                        WHERE product_id = ?
                    ");
                    $stmt->execute([$id]);
                    break;
                case 'Role':
                    $stmt = $pdo->prepare("SELECT id AS _id, Name FROM Role WHERE id = ?");
                    $stmt->execute([$id]);
                    break;
                case 'News':
                    $stmt = $pdo->prepare("
                        SELECT news_id AS _id, title, content, image_url, created_at
                        FROM News
                        WHERE news_id = ?
                    ");
                    $stmt->execute([$id]);
                    break;
                case 'Promotions':
                    $stmt = $pdo->prepare("
                        SELECT promotion_id AS _id, title, description, image_url, start_date, end_date, status
                        FROM Promotions
                        WHERE promotion_id = ?
                    ");
                    $stmt->execute([$id]);
                    break;
                default:
                    $idColumn = $table === 'Users' ? 'user_id' : ($table === 'Orders' ? 'order_id' : ($table === 'Reviews' ? 'review_id' : strtolower($table) . '_id'));
                    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $idColumn = ?");
                    $stmt->execute([$id]);
            }
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } elseif ($table === 'Role_Permissions' && $roleId) {
            if (!checkRolePermission($role_id, 'edit_role')) {
                die(json_encode(['error' => 'Недостаточно прав']));
            }
            $stmt = $pdo->prepare("SELECT * FROM Role_Permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } elseif ($table === 'Product_Characteristics' && $categoryId) {
            $stmt = $pdo->prepare("SELECT characteristic_id AS _id, name FROM Product_Characteristics WHERE category_id = ?");
            $stmt->execute([$categoryId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } elseif ($table === 'Products' && $categoryId) {
            $stmt = $pdo->prepare("SELECT product_id AS _id, name FROM Products WHERE category_id = ?");
            $stmt->execute([$categoryId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } else {
            handleAnalytics($pdo, $table, $role_id);
        }
        break;
    default:
        echo json_encode(['error' => 'Неверное действие']);
}