<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$data = json_decode(file_get_contents('php://input'), true);
$table = sanitize($data['table'] ?? '');
$id = (int)($data['id'] ?? 0);
$fields = $data['fields'] ?? [];

$allowedTables = [
    'Products' => 'edit_product',
    'Orders' => 'edit_order',
    'Users' => 'edit_user',
    'Categories' => 'edit_category',
    'News' => 'edit_news',
    'Promotions' => 'edit_promotion',
    'Reviews' => 'edit_review',
    'Role' => 'edit_role',
    'Role_Permissions' => 'edit_role_permission',
    'User_Sessions' => 'edit_user_session',
    'Order_Items' => 'edit_order_item',
    'Product_Characteristics' => 'edit_characteristic'
];

if (!isset($allowedTables[$table]) || $id <= 0 || empty($fields) || (!in_array($allowedTables[$table], $permissions) && !in_array('admin_full_access', $permissions))) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

try {
    global $pdo;
    $set = [];
    $params = [];
    foreach ($fields as $field => $value) {
        $set[] = "$field = ?";
        $params[] = sanitize($value);
    }
    $params[] = $id;

    $query = "UPDATE $table SET " . implode(', ', $set) . " WHERE {$table}_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
}
?>