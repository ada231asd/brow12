<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'getPromotions':
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name 
                FROM Promotions p
                LEFT JOIN Categories c ON p.category_id = c.category_id
            ");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case 'addPromotion':
            $stmt = $pdo->prepare("
                INSERT INTO Promotions 
                (title, description, start_date, end_date, category_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['category_id']
            ]);
            echo json_encode(['status' => 'success']);
            break;

        case 'updatePromotionStatus':
            $stmt = $pdo->prepare("
                UPDATE Promotions 
                SET status = ? 
                WHERE promotion_id = ?
            ");
            $stmt->execute([$_GET['status'], $_GET['id']]);
            echo json_encode(['status' => 'success']);
            break;

        case 'deletePromotion':
            $stmt = $pdo->prepare("
                DELETE FROM Promotions 
                WHERE promotion_id = ?
            ");
            $stmt->execute([$_GET['id']]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}