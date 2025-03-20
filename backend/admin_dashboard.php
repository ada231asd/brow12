<?php
require 'ajax/db.php';

// Запрос для получения хитов продаж
$sql_hits = "SELECT * FROM Products WHERE is_bestseller = 1 LIMIT 4";
$stmt_hits = $pdo->query($sql_hits);
$hits = $stmt_hits->fetchAll();

// Запрос для получения новинок
$sql_new = "SELECT * FROM Products WHERE is_new = 1 LIMIT 4";
$stmt_new = $pdo->query($sql_new);
$new = $stmt_new->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <style>
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        .card {
            width: 326px;
            border: 1px solid #ccc;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card img {
            width: 100%;
            height: auto;
        }
        .card h3 {
            margin: 10px 0;
        }
        .card .price {
            font-size: 1.2em;
            color: #333;
        }
        .card .old-price {
            text-decoration: line-through;
            color: #999;
        }
        .card .discount {
            color: red;
        }
        .card .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .card .buttons button {
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Хиты продаж</h1>
    <div class="container">
        <?php foreach ($hits as $product): ?>
            <div class="card">
                <div class="badge">Хит продаж</div>
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <div class="rating">★★★★☆ (42)</div>
                <div class="price">
                    <?php if ($product['discount'] > 0): ?>
                        <span class="old-price"><?= number_format($product['price'], 2, '.', ' ') ?> руб.</span>
                        <span class="new-price"><?= number_format($product['price'] * (1 - $product['discount'] / 100), 2, '.', ' ') ?> руб.</span>
                        <span class="discount">-<?= $product['discount'] ?>%</span>
                    <?php else: ?>
                        <?= number_format($product['price'], 2, '.', ' ') ?> руб.
                    <?php endif; ?>
                </div>
                <div class="buttons">
                    <button>❤️ В избранное</button>
                    <button>⚖️ Сравнить</button>
                    <button>🛒 Купить</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h1>Новинки</h1>
    <div class="container">
        <?php foreach ($new as $product): ?>
            <div class="card">
                <div class="badge">Новинка</div>
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <div class="rating">★★★★☆ (42)</div>
                <div class="price">
                    <?php if ($product['discount'] > 0): ?>
                        <span class="old-price"><?= number_format($product['price'], 2, '.', ' ') ?> руб.</span>
                        <span class="new-price"><?= number_format($product['price'] * (1 - $product['discount'] / 100), 2, '.', ' ') ?> руб.</span>
                        <span class="discount">-<?= $product['discount'] ?>%</span>
                    <?php else: ?>
                        <?= number_format($product['price'], 2, '.', ' ') ?> руб.
                    <?php endif; ?>
                </div>
                <div class="buttons">
                    <button>❤️ В избранное</button>
                    <button>⚖️ Сравнить</button>
                    <button>🛒 Купить</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>