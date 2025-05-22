<?php
header('Content-Type: application/json; charset=utf-8');
$servername = "127.0.0.1:3306";
$username = "root";
$password = "";
$dbname = "pk_st";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");
    $stmt = $conn->prepare("SELECT store_id, name, address, latitude, longitude, working_hours FROM Stores WHERE status = 'Активен'");
    $stmt->execute();
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["stores" => $stores]);
} catch (PDOException $e) {
    echo json_encode(["stores" => [], "error" => $e->getMessage()]);
} 