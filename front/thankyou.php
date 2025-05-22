<?php
$orderCode = isset($_GET['order_code']) ? htmlspecialchars($_GET['order_code']) : '';
// Добавляем отладочную информацию
error_log("Order Code: " . $orderCode);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Спасибо за покупку!</title>
    <meta http-equiv="refresh" content=";url=../index.php">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .thankyou-box { 

            padding: 40px 60px; 
            border-radius: 16px; 
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); 
            text-align: center;
            max-width: 600px;
            margin: 40px auto;
        }
        .thankyou-title { 
            font-size: 2.2rem; 
            margin-bottom: 16px; 
            color: #2e7d32; 
            font-weight: 600;
        }
        .order-code { 
            font-size: 1.2rem; 
            color: #555; 
            margin-bottom: 24px; 
            padding: 10px;
            border-radius: 8px;
        }
        .redirect { 
            color: #888; 
            font-size: 1rem;
            margin-top: 20px;
        }
        .gif-container {
            margin: 20px 0;
            max-width: 300px;
            margin: 0 auto;
            background: transparent;
        }
        .tenor-gif-embed {
            background: transparent !important;
        }
    </style>
</head>
<body>
<?php require '../Header/header.html'; ?>
    <div class="thankyou-box">
        <div class="thankyou-title">Спасибо за покупку!</div>
        <div class="gif-container">
            <div class="tenor-gif-embed" data-postid="22838888" data-share-method="host" data-aspect-ratio="1" data-width="100%">
                <a href="https://tenor.com/view/tkthao219-bubududu-panda-gif-22838888">Tkthao219 Bubududu Sticker</a>
                from <a href="https://tenor.com/search/tkthao219-stickers">Tkthao219 Stickers</a>
            </div>
            <script type="text/javascript" async src="https://tenor.com/embed.js"></script>
        </div>
        <?php if (!empty($orderCode)): ?>
            <div class="order-code">Ваш номер заказа: <b><?php echo $orderCode; ?></b></div>
        <?php else: ?>
            <div class="order-code">Номер заказа не найден</div>
        <?php endif; ?>
        <div class="redirect">Вы будете перенаправлены на главную страницу через 5 секунд...</div>
    </div>
<?php require '../Foter/foter.html';?>
</body>
</html> 