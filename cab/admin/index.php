<?php
require_once __DIR__ . '/auth.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$userData = getUserData();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main>
            <?php
            $page = $_GET['page'] ?? 'dashboard';
            switch ($page) {
                case 'dashboard':
                    include 'dashboard.php';
                    break;
                case 'tables':
                    include 'tables.php';
                    break;
                case 'reviews':
                    include 'reviews.php';
                    break;
                case 'roles':
                    include 'roles.php';
                    break;
                default:
                    echo '<h2>Страница не найдена</h2>';
            }
            ?>
        </main>
    </div>
    <?php include 'footer.php'; ?>
    <?php include 'modals.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./js/main.js"></script>
    <script src="./js/charts.js"></script>
    <?php if ($page === 'dashboard'): ?>
        <script src="./js/dashboard.js"></script>
    <?php endif; ?>
    <?php if ($page === 'roles'): ?>
        <script src="./js/roles.js"></script>
    <?php endif; ?>
    <?php if ($page === 'tables'): ?>
        <script src="./js/tables.js"></script>
    <?php endif; ?>
    <?php if ($page === 'reviews'): ?>
        <script src="./js/reviews.js"></script>
    <?php endif; ?>
</body>
</html>