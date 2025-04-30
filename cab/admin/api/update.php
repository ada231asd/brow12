<?php
function handleUpdate($pdo, $table, $role_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die(json_encode(['error' => 'Метод не поддерживается']));
    }

    $hasPermission = checkRolePermission($role_id, "edit_$table");
    if (!$hasPermission) {
        die(json_encode(['error' => "Недостаточно прав для edit_$table, role_id: $role_id"]));
    }

    $id = $_GET['id'] ?? null;
    if (!$id) {
        die(json_encode(['error' => 'Не указан ID']));
    }

    try {
        $data = [];
        foreach ($_POST as $key => $value) {
            if ($key === 'role_name') continue; // Пропускаем role_name, используем role_id
            $data[$key] = $value;
        }

        // Обработка загрузки файла
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'full_image/' . strtolower($table) . '/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '-' . basename($_FILES['image_url']['name']);
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image_url']['tmp_name'], $filePath)) {
                $data['image_url'] = $filePath;
            } else {
                die(json_encode(['error' => 'Ошибка загрузки файла']));
            }
        }

        $idColumn = $table === 'Users' ? 'user_id' : ($table === 'Orders' ? 'order_id' : ($table === 'Reviews' ? 'review_id' : strtolower($table) . '_id'));
        $setClause = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $stmt = $pdo->prepare("UPDATE $table SET $setClause WHERE $idColumn = ?");
        $stmt->execute([...array_values($data), $id]);

        // Логирование действия
        $admin_id = $userData['user_id'] ?? null;
        if ($admin_id) {
            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action, table_name, record_id, action_type) VALUES (?, ?, ?, ?, 'UPDATE')");
            $stmt->execute([$admin_id, "Обновил запись ID $id", $table, $id]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Ошибка обновления: ' . $e->getMessage()]);
    }
}