<?php
require_once '/../../backend/ajax/db.php';

$stmt = $pdo->query("SELECT MIN(price) AS min, MAX(price) AS max FROM Products");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($result);