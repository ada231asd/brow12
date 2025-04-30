<?php
session_start();
require_once 'db_connect.php';
require_once 'config.php';

function checkAuth() {
    if (!isset($_COOKIE['session_token']) || !isset($_COOKIE['user_id'])) {
        return false;
    }

    global $pdo;
    $token = sanitize($_COOKIE['session_token']);
    $user_id = (int)$_COOKIE['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM User_Sessions WHERE user_id = ? AND token = ? AND expires_at > NOW()");
    $stmt->execute([$user_id, $token]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        return false;
    }

    $_SESSION['user_id'] = $user_id;
    $_SESSION['role_id'] = getUserRole($user_id);
    return true;
}

function getUserRole($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT role_id FROM Users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getUserPermissions($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT permission FROM Role_Permissions WHERE role_id = (SELECT role_id FROM Users WHERE user_id = ?)");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function hasPermission($section, $permissions, $sectionPermissions) {
    if (in_array('admin_full_access', $permissions)) {
        return true;
    }
    foreach ($sectionPermissions[$section] ?? [] as $perm) {
        if (in_array($perm, $permissions)) {
            return true;
        }
    }
    return false;
}

function logout() {
    global $pdo;
    if (isset($_COOKIE['session_token'])) {
        $stmt = $pdo->prepare("DELETE FROM User_Sessions WHERE token = ?");
        $stmt->execute([$_COOKIE['session_token']]);
    }
    setcookie('session_token', '', time() - 3600, '/');
    setcookie('user_id', '', time() - 3600, '/');
    session_destroy();
}
?>