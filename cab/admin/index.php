<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Проверка авторизации
if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

// Получение прав пользователя
$userId = $_COOKIE['user_id'];
$permissions = getUserPermissions($userId);

// Проверка, является ли пользователь админом
$stmt = $pdo->prepare("SELECT role_id FROM Users WHERE user_id = ?");
$stmt->execute([$userId]);
$userRole = $stmt->fetchColumn();
$isAdmin = ($userRole == 1 || in_array('admin_full_access', $permissions));

$sections = [
    'dashboard' => true,
    'products' => ['get_products', 'add_product', 'edit_product', 'delete_product'],
    'orders' => ['get_orders', 'edit_order', 'delete_order'],
    'users' => ['get_users', 'add_user', 'edit_user', 'delete_user'],
    'categories' => ['get_categories', 'add_category', 'edit_category', 'delete_category'],
    'news' => ['get_news', 'add_news', 'edit_news', 'delete_news'],
    'promotions' => ['get_promotions', 'add_promotion', 'edit_promotion', 'delete_promotion'],
    'reviews' => ['get_reviews', 'add_review', 'edit_review', 'delete_review'],
    'roles' => ['get_roles', 'add_role', 'edit_role', 'delete_role'],
    'role_permissions' => ['get_role_permissions', 'add_role_permission', 'edit_role_permission', 'delete_role_permission'],
    'user_sessions' => ['get_user_sessions', 'delete_user_session'],
    'order_items' => ['get_order_items', 'add_order_item', 'edit_order_item', 'delete_order_item'],
    'characteristics' => ['get_characteristics', 'add_characteristic', 'edit_characteristic', 'delete_characteristic']
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель: Цифровой Хаос</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Статичное боковое меню -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>Админ-панель</h3>
        </div>
        <ul class="sidebar-links">
            <?php foreach ($sections as $section => $perms): ?>
                <?php if ($isAdmin || $section === 'dashboard' || hasPermission($section, $permissions, $sections)): ?>
                    <li><a href="#" class="sidebar-link" data-section="<?php echo $section; ?>"><?php echo ucfirst($section); ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
            <li><a href="/cab/admin/logout" class="sidebar-link">Выйти</a></li>
        </ul>
    </nav>

    <!-- Основной контейнер для компонентов -->
    <main class="content">
        <section id="active-section" class="component-section">
            <?php include "components/dashboard.php"; ?>
        </section>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>