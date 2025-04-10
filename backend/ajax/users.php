<?php
include 'db.php';

$action = $_GET['action'] ?? '';

if ($action == 'load') {
    // Исправлено: role -> user_role
    $stmt = $pdo->query("SELECT * FROM Users WHERE user_role != 'Администратор'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$row['user_id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['email']}</td>
                <td>{$row['user_role']}</td> <!-- Исправлено -->
                <td>
                    <button onclick=\"openEditModal('{$row['user_id']}', '{$row['name']}', '{$row['email']}', '{$row['user_role']}')\">Редактировать</button>
                    <button onclick=\"deleteUser('{$row['user_id']}')\">Удалить</button>
                </td>
              </tr>";
    }
} elseif ($action == 'search') {
    $query = $_GET['query'] ?? '';
    // Исправлено: role -> user_role
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE (name LIKE ? OR email LIKE ?) AND user_role != 'Администратор'");
    $stmt->execute(["%$query%", "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        foreach ($results as $row) {
            echo "<tr>
                    <td>{$row['user_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['user_role']}</td> <!-- Исправлено -->
                    <td>
                        <button onclick=\"openEditModal('{$row['user_id']}', '{$row['name']}', '{$row['email']}', '{$row['user_role']}')\">Редактировать</button>
                        <button onclick=\"deleteUser('{$row['user_id']}')\">Удалить</button>
                    </td>
                  </tr>";
        }
    } else {
        echo ""; 
    }
} elseif ($action == 'save') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($role === 'Администратор') {
        die('Назначение администратора запрещено.');
    }

    if (empty($password)) {
        $password = password_hash('123', PASSWORD_BCRYPT);
    } else {
        $password = password_hash($password, PASSWORD_BCRYPT);
    }

    if ($id) {
        // Исправлено: role -> user_role
        $stmt = $pdo->prepare("UPDATE Users SET name = ?, email = ?, user_role = ? WHERE user_id = ?");
        $stmt->execute([$name, $email, $role, $id]);
    } else {
        // Исправлено: role -> user_role
        $stmt = $pdo->prepare("INSERT INTO Users (name, email, password_hash, user_role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
    }
} elseif ($action == 'delete') {
    $id = $_GET['id'] ?? '';

    // Исправлено: role -> user_role
    $stmtCheck = $pdo->prepare("SELECT user_role FROM Users WHERE user_id = ?");
    $stmtCheck->execute([$id]);
    $user = $stmtCheck->fetch();

    if ($user['user_role'] === 'Администратор') {
        die('Удаление администратора запрещено.');
    }

    $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
    $stmt->execute([$id]);
}
?>