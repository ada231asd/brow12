<?php
include 'db.php';

$action = $_GET['action'] ?? '';
$statusFilter = $_GET['status'] ?? '';

if ($action == 'load') {
    $query = "SELECT Reviews.review_id, Users.name AS user_name, Reviews.comment, Reviews.rating, Reviews.status FROM Reviews JOIN Users ON Reviews.user_id = Users.user_id";
    $params = [];

    if ($statusFilter !== '') {
        $query .= " WHERE Reviews.status = ?";
        $params[] = $statusFilter;
    }
    $query .= " ORDER BY Reviews.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$row['review_id']}</td>
                <td>{$row['user_name']}</td>
                <td>{$row['comment']}</td>
                <td>{$row['rating']}</td>
                <td>{$row['status']}</td>
                <td>";

        if ($row['status'] === 'На модерации') {
            echo "<button onclick=\"approveReview('{$row['review_id']}')\">Одобрить</button>
                  <button onclick=\"rejectReview('{$row['review_id']}')\">Отклонить</button>";
        }

        echo "<button onclick=\"deleteReview('{$row['review_id']}')\">Удалить</button></td></tr>";
    }
} elseif ($action == 'search') {
    $query = "SELECT Reviews.review_id, Users.name AS user_name, Reviews.comment, Reviews.rating, Reviews.status FROM Reviews JOIN Users ON Reviews.user_id = Users.user_id WHERE (Reviews.comment LIKE ?)";
    $params = ["%" . ($_GET['query'] ?? '') . "%"];

    if ($statusFilter !== '') {
        $query .= " AND Reviews.status = ?";
        $params[] = $statusFilter;
    }
    $query .= " ORDER BY Reviews.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$row['review_id']}</td>
                <td>{$row['user_name']}</td>
                <td>{$row['comment']}</td>
                <td>{$row['rating']}</td>
                <td>{$row['status']}</td>
                <td>";

        if ($row['status'] === 'На модерации') {
            echo "<button onclick=\"approveReview('{$row['review_id']}')\">Одобрить</button>
                  <button onclick=\"rejectReview('{$row['review_id']}')\">Отклонить</button>";
        }

        echo "<button onclick=\"deleteReview('{$row['review_id']}')\">Удалить</button></td></tr>";
    }
} elseif ($action == 'approve') {
    $id = $_GET['id'] ?? '';
    $stmt = $pdo->prepare("UPDATE Reviews SET status = 'Одобрен' WHERE review_id = ?");
    $stmt->execute([$id]);
} elseif ($action == 'reject') {
    $id = $_GET['id'] ?? '';
    $stmt = $pdo->prepare("UPDATE Reviews SET status = 'Отклонен' WHERE review_id = ?");
    $stmt->execute([$id]);
} elseif ($action == 'delete') {
    $id = $_GET['id'] ?? '';
    $stmt = $pdo->prepare("DELETE FROM Reviews WHERE review_id = ?");
    $stmt->execute([$id]);
}
?>
