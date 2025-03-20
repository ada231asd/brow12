<?php
include 'db.php';

$action = $_GET['action'] ?? '';

if ($action == 'load') {
    $stmt = $pdo->query("SELECT * FROM News ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$row['news_id']}</td>
                <td>{$row['title']}</td>
                <td>{$row['content']}</td>
                <td>{$row['created_at']}</td>
                <td>
                    <button onclick=\"openEditModal('{$row['news_id']}', '{$row['title']}', '{$row['content']}')\">Редактировать</button>
                    <button onclick=\"deleteNews('{$row['news_id']}')\">Удалить</button>
                </td>
              </tr>";
    }
} elseif ($action == 'search') {
    $query = $_GET['query'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM News WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$query%", "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        foreach ($results as $row) {
            echo "<tr>
                    <td>{$row['news_id']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['content']}</td>
                    <td>{$row['created_at']}</td>
                    <td>
                        <button onclick=\"openEditModal('{$row['news_id']}', '{$row['title']}', '{$row['content']}')\">Редактировать</button>
                        <button onclick=\"deleteNews('{$row['news_id']}')\">Удалить</button>
                    </td>
                  </tr>";
        }
    } else {
        echo "";  // Оставляем пустым, чтобы отобразить сообщение об отсутствии результатов
    }
} elseif ($action == 'save') {
    $id = $_POST['id'] ?? '';
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    if ($id) {
        $stmt = $pdo->prepare("UPDATE News SET title = ?, content = ? WHERE news_id = ?");
        $stmt->execute([$title, $content, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO News (title, content, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$title, $content]);
    }
} elseif ($action == 'delete') {
    $id = $_GET['id'] ?? '';
    $stmt = $pdo->prepare("DELETE FROM News WHERE news_id = ?");
    $stmt->execute([$id]);
}
?>