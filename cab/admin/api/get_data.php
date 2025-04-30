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
$table = sanitize($_GET['table'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$search = sanitize($_GET['search'] ?? '');

$allowedTables = [
    'Products' => 'get_products',
    'Orders' => 'get_orders',
    'Users' => 'get_users',
    'Categories' => 'get_categories',
    'News' => 'get_news',
    'Promotions' => 'get_promotions',
    'Reviews' => 'get_reviews',
    'Role' => 'get_roles',
    'Role_Permissions' => 'get_role_permissions',
    'User_Sessions' => 'get_user_sessions',
    'Order_Items' => 'get_order_items',
    'Product_Characteristics' => 'get_characteristics'
];

if (!isset($allowedTables[$table]) || (!in_array($allowedTables[$table], $permissions) && !in_array('admin_full_access', $permissions))) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

try {
    $result = getPaginatedData($table, $search, $page);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
}
?>