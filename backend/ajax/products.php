<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getProducts':
        $stmt = $pdo->query("SELECT * FROM Products");
        echo json_encode($stmt->fetchAll());
        break;

    case 'searchProducts':
        $query = $_GET['query'];
        $stmt = $pdo->prepare("SELECT * FROM Products WHERE name LIKE ?");
        $stmt->execute(["%$query%"]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'deleteProduct':
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM Products WHERE product_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
        break;

        case 'getProductDetails':
            $id = $_GET['id'];
            $stmt = $pdo->prepare("
                SELECT 
                    p.*, 
                    pc.name AS characteristic_name, 
                    pcv.value 
                FROM Products p
                LEFT JOIN Product_Characteristic_Values pcv 
                    ON p.product_id = pcv.product_id
                LEFT JOIN Product_Characteristics pc 
                    ON pcv.characteristic_id = pc.characteristic_id
                WHERE p.product_id = ?
            ");
            $stmt->execute([$id]);
            $productData = $stmt->fetchAll();
        
            // Группируем характеристики
            $product = [
                'product_id' => $productData[0]['product_id'],
                'name' => $productData[0]['name'],
                'price' => $productData[0]['price'],
                'description' => $productData[0]['description'],
                'image_url' => $productData[0]['image_url'],
                'stock_quantity' => $productData[0]['stock_quantity'],
                'characteristics' => []
            ];
        
            foreach ($productData as $row) {
                if ($row['characteristic_name']) {
                    $product['characteristics'][] = [
                        'name' => $row['characteristic_name'],
                        'value' => $row['value']
                    ];
                }
            }
        
            echo json_encode($product);
            break;

    case 'getCategories':
        $stmt = $pdo->query("SELECT * FROM Categories");
        echo json_encode($stmt->fetchAll());
        break;

    case 'getCharacteristics':
        $categoryId = $_GET['categoryId'];
        $stmt = $pdo->prepare("SELECT * FROM Product_Characteristics WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'addProduct':
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $categoryId = $_POST['category'];
        $stockQuantity = $_POST['stock_quantity'];
        $image = $_FILES['image'];

        // Путь к папке uploads
        $uploadDir = __DIR__ . '/uplods/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imageName = basename($image['name']);
        $imageUrl = 'ajax/uploads/' . $imageName; // Исправленный путь
        $uploadFilePath = $uploadDir . $imageName;

        if (move_uploaded_file($image['tmp_name'], $uploadFilePath)) {
            $stmt = $pdo->prepare("INSERT INTO Products (name, price, description, category_id, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $description, $categoryId, $stockQuantity, $imageUrl]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['error' => 'Ошибка загрузки изображения']);
        }
        break;

    case 'updateProduct':
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $categoryId = $_POST['category'];
        $stockQuantity = $_POST['stock_quantity'];
        $image = $_FILES['image'];

        // Если новое изображение загружено
        if ($image['size'] > 0) {
            $uploadDir = __DIR__ . '/uploads/';
            $imageName = basename($image['name']);
            $imageUrl = 'ajax/uploads/' . $imageName;
            $uploadFilePath = $uploadDir . $imageName;

            if (move_uploaded_file($image['tmp_name'], $uploadFilePath)) {
                $stmt = $pdo->prepare("UPDATE Products SET name = ?, price = ?, description = ?, category_id = ?, stock_quantity = ?, image_url = ? WHERE product_id = ?");
                $stmt->execute([$name, $price, $description, $categoryId, $stockQuantity, $imageUrl, $id]);
            } else {
                echo json_encode(['error' => 'Ошибка загрузки изображения']);
                break;
            }
        } else {
            // Если изображение не обновлено
            $stmt = $pdo->prepare("UPDATE Products SET name = ?, price = ?, description = ?, category_id = ?, stock_quantity = ? WHERE product_id = ?");
            $stmt->execute([$name, $price, $description, $categoryId, $stockQuantity, $id]);
        }

        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}


?>