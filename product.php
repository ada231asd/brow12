<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница товара</title>
    <link rel="stylesheet" href="/brow12/css/style.css">
</head>
<body>
<?php require 'Header/header.html'; ?>
    <div class="custom-container my-5">
        <a href="#" class="back-button back-btn text-decoration-none">← Назад</a>
        <div class="product-grid">
                <img class="product-image img-fluid" alt="Товар">
            <div class="product-column">
                <h1 class="product-name">Название товара</h1>
                <div class="product-actions">
                    <div class="rating-and-actions">
                        <div class="rating-section">
                            <div class="product-rating"></div>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M19 13C19 13.5304 18.7893 14.0391 18.4142 14.4142C18.0391 14.7893 17.5304 15 17 15H5L1 19V3C1 2.46957 1.21071 1.96086 1.58579 1.58579C1.96086 1.21071 2.46957 1 3 1H17C17.5304 1 18.0391 1.21071 18.4142 1.58579C18.7893 1.96086 19 2.46957 19 3V13Z" stroke="#FFFCFC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
                            <div class="review-count"></div>
                        </div>
                        <div class="action-buttons">
                            <button class="custom-btn btn-favorite favorite-btn" data-product-id="">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect x="0.5" y="0.5" width="47" height="47" rx="3.5" stroke="#C8CACB"/>
<path d="M32.1603 17.0002C31.1002 15.9374 29.6951 15.2887 28.1986 15.1713C26.7021 15.054 25.213 15.4756 24.0003 16.3602C22.7279 15.4138 21.1443 14.9847 19.5682 15.1592C17.9921 15.3337 16.5407 16.0989 15.5062 17.3008C14.4718 18.5026 13.9311 20.0517 13.9931 21.6362C14.0551 23.2207 14.7151 24.7228 15.8403 25.8402L22.0503 32.0602C22.5703 32.5719 23.2707 32.8588 24.0003 32.8588C24.7299 32.8588 25.4303 32.5719 25.9503 32.0602L32.1603 25.8402C33.3279 24.6654 33.9832 23.0764 33.9832 21.4202C33.9832 19.7639 33.3279 18.1749 32.1603 17.0002ZM30.7503 24.4602L24.5403 30.6702C24.4696 30.7415 24.3855 30.7982 24.2928 30.8368C24.2001 30.8755 24.1007 30.8954 24.0003 30.8954C23.8999 30.8954 23.8004 30.8755 23.7077 30.8368C23.615 30.7982 23.5309 30.7415 23.4603 30.6702L17.2503 24.4302C16.466 23.6285 16.0269 22.5516 16.0269 21.4302C16.0269 20.3087 16.466 19.2318 17.2503 18.4302C18.0494 17.6412 19.1272 17.1987 20.2503 17.1987C21.3733 17.1987 22.4511 17.6412 23.2503 18.4302C23.3432 18.5239 23.4538 18.5983 23.5757 18.6491C23.6976 18.6998 23.8283 18.726 23.9603 18.726C24.0923 18.726 24.223 18.6998 24.3449 18.6491C24.4667 18.5983 24.5773 18.5239 24.6703 18.4302C25.4694 17.6412 26.5472 17.1987 27.6703 17.1987C28.7933 17.1987 29.8711 17.6412 30.6703 18.4302C31.4653 19.2213 31.9189 20.2924 31.9338 21.4139C31.9488 22.5353 31.5239 23.6181 30.7503 24.4302V24.4602Z" fill="#C8CACB"/>
</svg>

                            </button>
                            <button class="custom-btn btn-comparison comparison-btn" data-product-id="">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect x="0.5" y="0.5" width="47" height="47" rx="3.5" stroke="#C8CACB"/>
<path d="M17 24C16.7348 24 16.4804 24.1054 16.2929 24.2929C16.1054 24.4804 16 24.7348 16 25V33C16 33.2652 16.1054 33.5196 16.2929 33.7071C16.4804 33.8946 16.7348 34 17 34C17.2652 34 17.5196 33.8946 17.7071 33.7071C17.8946 33.5196 18 33.2652 18 33V25C18 24.7348 17.8946 24.4804 17.7071 24.2929C17.5196 24.1054 17.2652 24 17 24ZM22 14C21.7348 14 21.4804 14.1054 21.2929 14.2929C21.1054 14.4804 21 14.7348 21 15V33C21 33.2652 21.1054 33.5196 21.2929 33.7071C21.4804 33.8946 21.7348 34 22 34C22.2652 34 22.5196 33.8946 22.7071 33.7071C22.8946 33.5196 23 33.2652 23 33V15C23 14.7348 22.8946 14.4804 22.7071 14.2929C22.5196 14.1054 22.2652 14 22 14ZM32 28C31.7348 28 31.4804 28.1054 31.2929 28.2929C31.1054 28.4804 31 28.7348 31 29V33C31 33.2652 31.1054 33.5196 31.2929 33.7071C31.4804 33.8946 31.7348 34 32 34C32.2652 34 32.5196 33.8946 32.7071 33.7071C32.8946 33.5196 33 33.2652 33 33V29C33 28.7348 32.8946 28.4804 32.7071 28.2929C32.5196 28.1054 32.2652 28 32 28ZM27 20C26.7348 20 26.4804 20.1054 26.2929 20.2929C26.1054 20.4804 26 20.7348 26 21V33C26 33.2652 26.1054 33.5196 26.2929 33.7071C26.4804 33.8946 26.7348 34 27 34C27.2652 34 27.5196 33.8946 27.7071 33.7071C27.8946 33.5196 28 33.2652 28 33V21C28 20.7348 27.8946 20.4804 27.7071 20.2929C27.5196 20.1054 27.2652 20 27 20Z" fill="#C8CACB"/>
</svg>

                            </button>
                        </div>
                    </div>
                    <div class="price-and-buttons">
                        <div class="price-container"></div>
                        <div class="action-buttons">
                            <button class="custom-btn btn-primary-custom">В корзину</button>
                            <button class="custom-btn btn-secondary-custom btn-buy">Купить в 1 клик</button>
                        </div>
                    </div>
                </div>
                <p class="product-description"></p>
            </div>
        </div>
        <div class="tabs mt-5">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab="description">Описание</button>
                <button class="tab-button" data-tab="specifications">Характеристики</button>
                <button class="tab-button" data-tab="reviews">Отзывы</button>
            </div>
            <div class="tab-content">
                <div id="description" class="tab-pane active">
                    <p class="product-description-tab"></p>
                </div>
                <div id="specifications" class="tab-pane">
                    <ul class="specifications-list"></ul>
                </div>
                <div id="reviews" class="tab-pane">
                    <div class="reviews-list"></div>
                    <div class="review-form-container">
                        <h3>Оставить отзыв</h3>
                        <form id="reviewForm" class="review-form">
                            <div class="form-group">
                                <label for="rating">Оценка:</label>
                                <div class="star-rating">
                                    <input type="radio" name="rating" id="star5" value="5" required>
                                    <label for="star5">★</label>
                                    <input type="radio" name="rating" id="star4" value="4">
                                    <label for="star4">★</label>
                                    <input type="radio" name="rating" id="star3" value="3">
                                    <label for="star3">★</label>
                                    <input type="radio" name="rating" id="star2" value="2">
                                    <label for="star2">★</label>
                                    <input type="radio" name="rating" id="star1" value="1">
                                    <label for="star1">★</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comment">Комментарий:</label>
                                <textarea id="comment" name="comment" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="custom-btn btn-primary-custom">Отправить отзыв</button>
                        </form>
                        <div id="notificationContainer" class="notification-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require 'Foter/foter.html';?>
    <script src="/brow12/js/product.js"></script>
</body>
</html>