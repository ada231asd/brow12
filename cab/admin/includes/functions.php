<?php
require_once 'db_connect.php';

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function getPaginatedData($table, $search = '', $page = 1, $perPage = 10) {
    global $pdo;
    $offset = ($page - 1) * $perPage;

    // Подготовка условий для поиска
    $conditions = [];
    $params = [];
    if ($search) {
        $columnsStmt = $pdo->query("SHOW COLUMNS FROM $table");
        $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($columns as $column) {
            if ($column !== 'password_hash') { // Исключаем чувствительные поля
                $conditions[] = "$column LIKE ?";
                $params[] = "%$search%";
            }
        }
    }

    // Формирование запроса
    $where = !empty($conditions) ? 'WHERE ' . implode(' OR ', $conditions) : '';
    $query = "SELECT * FROM $table $where LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    // Подсчет общего количества записей
    $countQuery = "SELECT COUNT(*) FROM $table $where";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute(array_slice($params, 0, -2));
    $total = $countStmt->fetchColumn();
    $pages = ceil($total / $perPage);

    return [
        'data' => $data,
        'page' => $page,
        'pages' => $pages,
        'total' => $total
    ];
}
?>