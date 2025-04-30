<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/api/db.php';

if (isLoggedIn()) {
    $userData = getUserData();
    $stmt = $pdo->prepare("DELETE FROM User_Sessions WHERE user_id = ?");
    $stmt->execute([$userData['user_id']]);
    setcookie(COOKIE_NAME, '', time() - 3600, '/');
}

header('Location: login.php');
exit;