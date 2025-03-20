<?php
include 'db.php';

$action = $_GET['action'] ?? '';
$statusFilter = $_GET['status'] ?? '';

if ($action == 'load') {
    $query = "SELECT Feedback.feedback_id, Feedback.message, Feedback.status, Feedback.response, Users.email AS contact_info FROM Feedback JOIN Users ON Feedback.user_id = Users.user_id";
    $params = [];

    if ($statusFilter !== '') {
        $query .= " WHERE Feedback.status = ?";
        $params[] = $statusFilter;
    }
    $query .= " ORDER BY Feedback.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$row['feedback_id']}</td>
                <td>{$row['message']}</td>
                <td>{$row['contact_info']}</td>
                <td>{$row['status']}</td>
                <td>{$row['response']}</td>
                <td>";

        if ($row['status'] === 'Зарегистрирован') {
            echo "<button onclick=\"openReplyModal('{$row['feedback_id']}', '{$row['message']}', '{$row['contact_info']}')\">Ответить</button>";
        } else {
            echo "Ответ недоступен";
        }

        echo "</td></tr>";
    }
} elseif ($action == 'search') {
    $query = "SELECT Feedback.feedback_id, Feedback.message, Feedback.status, Feedback.response, Users.email AS contact_info FROM Feedback JOIN Users ON Feedback.user_id = Users.user_id WHERE (Feedback.message LIKE ? OR Feedback.response LIKE ?)";
    $params = ["%" . ($_GET['query'] ?? '') . "%", "%" . ($_GET['query'] ?? '') . "%"];

    if ($statusFilter !== '') {
        $query .= " AND Feedback.status = ?";
        $params[] = $statusFilter;
    }
    $query .= " ORDER BY Feedback.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$row['feedback_id']}</td>
                <td>{$row['message']}</td>
                <td>{$row['contact_info']}</td>
                <td>{$row['status']}</td>
                <td>{$row['response']}</td>
                <td>";

        if ($row['status'] === 'Зарегистрирован') {
            echo "<button onclick=\"openReplyModal('{$row['feedback_id']}', '{$row['message']}', '{$row['contact_info']}')\">Ответить</button>";
        } else {
            echo "Ответ недоступен";
        }

        echo "</td></tr>";
    }
} elseif ($action == 'updateStatus') {
    $id = $_GET['id'] ?? '';
    $status = $_GET['status'] ?? '';
    $stmt = $pdo->prepare("UPDATE Feedback SET status = ? WHERE feedback_id = ?");
    $stmt->execute([$status, $id]);
} elseif ($action == 'respond') {
    $id = $_POST['id'] ?? '';
    $response = $_POST['response'] ?? '';

    $stmt = $pdo->prepare("UPDATE Feedback SET response = ?, status = 'Закрыт' WHERE feedback_id = ?");
    $stmt->execute([$response, $id]);
}
?>
