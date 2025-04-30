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
$fields = $data['fields'] ?? [];

$allowedTables = [
    'Products' => 'add_product',
    'Orders' => 'add_order',
    'Users' => 'add_user',
    'Categories' => 'add_category',
    'News' => 'add_news',
    'Promotions' => 'add_promotion',
    'Reviews' => 'add_review',
    'Role' => 'add_role',
    'Role_Permissions' => 'add_role_permission',
    'User_Sessions' => 'add_user_session',
    'Order_Items' => 'add_order_item',
    'Product_Characteristics' => 'add_characteristic'
];

if (!isset($allowedTables[$table]) || empty($fields) || (!in_array($allowedTables[$table], $permissions) && !in_array('admin_full_access', $permissions))) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

try {
    global $pdo;
    $columns = array_keys($fields);
    $placeholders = array_fill(0, count($columns), '?');
    $query = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $pdo->prepare($query);
    $stmt->execute(array_values($fields));
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
}
?>