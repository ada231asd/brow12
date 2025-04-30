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

$allowedTables = [
    'Products' => 'delete_product',
    'Orders' => 'delete_order',
    'Users' => 'delete_user',
    'Categories' => 'delete_category',
    'News' => 'delete_news',
    'Promotions' => 'delete_promotion',
    'Reviews' => 'delete_review',
    'Role' => 'delete_role',
    'Role_Permissions' => 'delete_role_permission',
    'User_Sessions' => 'delete_user_session',
    'Order_Items' => 'delete_order_item',
    'Product_Characteristics' => 'delete_characteristic'
];

if (!isset($allowedTables[$table]) || $id <= 0 || (!in_array($allowedTables[$table], $permissions) && !in_array('admin_full_access', $permissions))) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

try {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM $table WHERE {$table}_id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
}
?>