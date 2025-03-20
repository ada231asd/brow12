<?php
// Включите буферизацию
ob_start();

// Установите заголовок JSON
header('Content-Type: application/json');

// Подключите БД с абсолютным путем
require __DIR__ . '/../backend/ajax/db.php';

try {
    // Проверьте соединение с БД
    if (!$pdo) {
        throw new Exception('Ошибка подключения к БД');
    }

    // Запрос данных
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM News WHERE news_id = ?");
        $stmt->execute([$_GET['id']]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->query("SELECT * FROM News ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Отправьте JSON
    echo json_encode([
        'status' => 'success',
        'data' => $data ?: []
    ]);

} catch (PDOException $e) {
    // Логируйте ошибку
    error_log("Ошибка БД: " . $e->getMessage());
    
    // Отправьте JSON-ошибку
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка базы данных'
    ]);
} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    // Очистите буфер
    ob_end_flush();
}