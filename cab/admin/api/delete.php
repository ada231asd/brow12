<?php
function handleDelete($pdo, $table, $role_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        die(json_encode(['error' => 'Метод не поддерживается']));
    }

    $id = $_GET['id'] ?? null;
    if (!$id || !checkRolePermission($role_id, "delete_$table")) {
        die(json_encode(['error' => 'Недостаточно прав']));
    }

    try {
        $query = "DELETE FROM $table WHERE {$table}_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Ошибка удаления: ' . $e->getMessage()]);
    }
}