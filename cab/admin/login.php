<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (checkAuth()) {
    header('Location: /cab/admin/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    global $pdo;
    $stmt = $pdo->prepare("SELECT user_id, password_hash, role_id FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
        $stmt = $pdo->prepare("INSERT INTO User_Sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['user_id'], $token, $expires_at]);

        setcookie('session_token', $token, time() + 30 * 24 * 3600, '/');
        setcookie('user_id', $user['user_id'], time() + 30 * 24 * 3600, '/');
        header('Location: /cab/admin/');
        exit;
    } else {
        $error = 'Неверный email или пароль. Хаос не пропустит тебя.';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
    <link rel="stylesheet" href="/cab/admin/assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Вход: Дверь в цифровой ад</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Войти</button>
        </form>
    </div>
</body>
</html>