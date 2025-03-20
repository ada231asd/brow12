<?php
require 'ajax/db.php';

if (!isset($_GET['id']) {
    header("HTTP/1.0 404 Not Found");
    exit('Товар не найден');
}

$product_id = (int)$_GET['id'];

// Основная информация о товаре
$sql = "SELECT p.*, c.name AS category_name 
        FROM Products p
        JOIN Categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("HTTP/1.0 404 Not Found");
    exit('Товар не найден');
}

// Характеристики товара
$sql_features = "SELECT pc.name, pv.value 
                FROM Product_Characteristic_Values pv
                JOIN Product_Characteristics pc ON pv.characteristic_id = pc.characteristic_id
                WHERE pv.product_id = ?";
$stmt_features = $pdo->prepare($sql_features);
$stmt_features->execute([$product_id]);
$features = $stmt_features->fetchAll();

// Отзывы
$sql_reviews = "SELECT r.*, u.name AS user_name 
               FROM Reviews r
               JOIN Users u ON r.user_id = u.user_id
               WHERE r.product_id = ? AND r.status = 'Одобрен'";
$stmt_reviews = $pdo->prepare($sql_reviews);
$stmt_reviews->execute([$product_id]);
$reviews = $stmt_reviews->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <style>
        .product-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .product-main {
            display: flex;
            gap: 40px;
            margin-bottom: 40px;
        }
        .product-image {
            flex: 0 0 500px;
        }
        .product-image img {
            width: 100%;
            height: auto;
        }
        .product-info {
            flex: 1;
        }
        .price-block {
            font-size: 24px;
            margin: 20px 0;
        }
        .old-price {
            text-decoration: line-through;
            color: #999;
            margin-right: 15px;
        }
        .discount {
            color: #f00;
            font-weight: bold;
        }
        .features-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .features-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .features-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .review {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .rating-stars {
            color: #ffd700;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="product-container">
        <div class="product-main">
            <div class="product-image">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="category">Категория: <?= htmlspecialchars($product['category_name']) ?></div>
                
                <div class="price-block">
                    <?php if ($product['discount'] > 0): ?>
                        <span class="old-price"><?= number_format($product['price'], 2, '.', ' ') ?> руб.</span>
                        <?php
                            $discounted_price = $product['price'] * (1 - $product['discount'] / 100);
                            $economy = $product['price'] - $discounted_price;
                        ?>
                        <span class="new-price"><?= number_format($discounted_price, 2, '.', ' ') ?> руб.</span>
                        <div class="discount">Экономия <?= number_format($economy, 2, '.', ' ') ?> руб. (<?= $product['discount'] ?>%)</div>
                    <?php else: ?>
                        <span class="new-price"><?= number_format($product['price'], 2, '.', ' ') ?> руб.</span>
                    <?php endif; ?>
                </div>

                <h3>Характеристики</h3>
                <table class="features-table">
                    <?php foreach ($features as $feature): ?>
                    <tr>
                        <td><?= htmlspecialchars($feature['name']) ?></td>
                        <td><?= htmlspecialchars($feature['value']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <button style="padding: 15px 30px; background: #007bff; color: white; border: none; cursor: pointer;">
                    Добавить в корзину
                </button>
            </div>
        </div>

        <div class="description-section">
            <h2>Описание товара</h2>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>

        <div class="reviews-section">
            <h2>Отзывы (<?= count($reviews) ?>)</h2>
            <?php foreach ($reviews as $review): ?>
            <div class="review">
                <div class="rating-stars">
                    <?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?>
                </div>
                <div class="review-author"><?= htmlspecialchars($review['user_name']) ?></div>
                <div class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></div>
                <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>