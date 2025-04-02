<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
header("Access-Control-Allow-Credentials: true");

try {
    // Удаляем куку аутентификации
    setcookie('auth_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Если есть токен - удаляем сессию из БД
    if (!empty($_COOKIE['auth_token'])) {
        require_once __DIR__ . '/../backend/ajax/db.php';
        $stmt = $pdo->prepare("DELETE FROM User_Sessions WHERE token = ?");
        $stmt->execute([$_COOKIE['auth_token']]);
    }

    // Возвращаем успешный статус и URL для перенаправления
    echo json_encode([
        'status' => 'success',
        'redirect' => '/'  // Главная страница
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка при выходе: ' . $e->getMessage()
    ]);
    exit;
}
?>