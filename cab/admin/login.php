<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/api/db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT user_id, name, role_id, password_hash FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO User_Sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['user_id'], $token, date('Y-m-d H:i:s', strtotime('+30 days'))]);
        setUserCookie($user['user_id'], $user['name'], $user['role_id'], $token);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админ-панель</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Вход в админ-панель</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>