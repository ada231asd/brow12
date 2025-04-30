<?php
require_once __DIR__ . '/api/db.php';

function isLoggedIn() {
    if (!isset($_COOKIE[COOKIE_NAME])) {
        return false;
    }

    $userData = json_decode($_COOKIE[COOKIE_NAME], true);
    if (!$userData || !isset($userData['user_id']) || !isset($userData['token'])) {
        return false;
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM User_Sessions WHERE user_id = ? AND token = ? AND expires_at > NOW()");
    $stmt->execute([$userData['user_id'], $userData['token']]);
    return $stmt->fetchColumn() > 0;
}

function getUserData() {
    if (!isLoggedIn()) {
        return null;
    }
    return json_decode($_COOKIE[COOKIE_NAME], true);
}

function checkRolePermission($role_id, $permission) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Role_Permissions WHERE role_id = ? AND permission = ?");
    $stmt->execute([$role_id, $permission]);
    return $stmt->fetchColumn() > 0;
}

function setUserCookie($user_id, $name, $role_id, $token) {
    $userData = [
        'user_id' => $user_id,
        'user_name' => $name,
        'role_id' => $role_id,
        'token' => $token
    ];
    setcookie(COOKIE_NAME, json_encode($userData), time() + COOKIE_EXPIRE, '/');
}