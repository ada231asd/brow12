<?php
function handleCreate($pdo, $table, $role_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die(json_encode(['error' => 'Метод не поддерживается']));
    }

    $hasPermission = checkRolePermission($role_id, "add_$table");
    if (!$hasPermission) {
        die(json_encode(['error' => "Недостаточно прав для add_$table, role_id: $role_id"]));
    }

    try {
        $data = [];
        foreach ($_POST as $key => $value) {
            if ($key === 'role_name') continue; // Пропускаем role_name
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

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($data));

        $newId = $pdo->lastInsertId();

        // Логирование действия
        $admin_id = $userData['user_id'] ?? null;
        if ($admin_id) {
            $stmt = $pdo->prepare("INSERT INTO Admin_Logs (admin_id, action, table_name, record_id, action_type) VALUES (?, ?, ?, ?, 'CREATE')");
            $stmt->execute([$admin_id, "Добавил запись ID $newId", $table, $newId]);
        }

        echo json_encode(['success' => true, 'id' => $newId]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Ошибка создания: ' . $e->getMessage()]);
    }
}