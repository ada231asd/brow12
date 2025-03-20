<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DimTech</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require 'Header/header.html'; ?>
    <section class="header_wrapper">
        <div class="mascot-container">
            <img src="img/mascots.png" class="mascot-image" alt="Маскот DimTech">
            <div class="speech-bubble">
                <p class="typing-text">Хей! Я маскот магазина DimTech. Буду очень рада, если заглянешь в наш каталог <br/>и что-нибудь приобретёшь.</p>
            </div>
        </div>
        <div class="slider-container">
            <div class="slide active">
                <img src="img/slides/image.png" alt="Slide 1">
            </div>
            <div class="slide">
                <img src="img/slides/image copy.png" alt="Slide 2">
            </div>
            <div class="slide">
                <img src="img/slides/image copy 2.png" alt="Slide 3">
            </div>
            <div class="slide">
                <img src="img/slides/image copy 3.png" alt="Slide 4">
            </div>
            <div class="slider-points">
                <span class="point active"></span>
                <span class="point"></span>
                <span class="point"></span>
                <span class="point"></span>
            </div>
        </div>
    </section>

    <section class="hit_p">
        <div class="hitp">
            <h1>Хиты продаж</h1>
            <a href="front/catalog.php">Все товары ></a>
        </div>
        <div id="hits-container" class="products-grid"></div>
    </section>

    <section class="new_p">
        <div class="newp">
           <h1>Новинки</h1> 
           <a href="front/catalog.php">Все товары ></a>
        </div>
        <div id="new-container" class="products-grid"></div>
    </section>

    <section class="ckud">
        <div class="blok_sk1">
            <p>Скидка 25% для новых пользователей на любой заказ</p>
        </div>
        <div class="blok_sk2">
            <p>Легкая рассрочка 0|0|18 Беспокоиться не о чем</p>
        </div>
    </section>

    <section class="category_p">
        <div class="category_header">
            <h1>Готовые ПК</h1>
            <a href="front/catalog.php">Все товары ></a>
        </div>
        <div id="category-1-container" class="products-grid"></div>
    </section>

    <section class="category_p">
        <div class="category_header">
            <h1>Мыши</h1>
            <a href="front/catalog.php">Все товары ></a>
        </div>
        <div id="category-5-container" class="products-grid"></div>
    </section>
    <section class="nn">
        <div class="blok_sk3">
            <p>Успей на нашу распродажу 12,05,2025 скидки до 50% на все</p>
        </div>
        <div class="blok_sk4">
            <p>Удивительный подарок ждет тебя в каждом заказе</p>
        </div>
    </section>
<section class="category_p">
    <div class="category_header">
        <h1>Процессоры</h1>
        <a href="front/catalog.php">Все товары ></a>
    </div>
    <div id="category-3-container" class="products-grid"></div>
</section>
<section class="category_p">
    <div class="category_header">
        <h1>Новости</h1>
        <a href="">Подробней ></a>
    </div>
</section>
<section class="nn">
        <div class="blok_sk3">
            <p>Успей на нашу распродажу 12,05,2025 скидки до 50% на все</p>
        </div>
        <div class="blok_sk4">
            <p>Удивительный подарок ждет тебя в каждом заказе</p>
        </div>
    </section>
<?php require 'Foter/foter.html';?>
<script>
      // Функция для загрузки продуктов
        async function loadProducts(params, containerId) {
     try {
        const url = new URL('products.php', window.location.href);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.status === 'success') {
            renderProducts(data.data, containerId);
        } else {
            console.error('Ошибка загрузки:', data.message);
        }
    } catch (error) {
        console.error('Ошибка загрузки:', error);
    }
}


        // Функция рендеринга товаров
        function renderProducts(products, containerId) {
            const container = document.getElementById(containerId);
            container.innerHTML = ''; // Очистка контейнера перед добавлением новых товаров

            products.forEach(product => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.addEventListener('click', (e) => {
                    if (!e.target.closest('.btn')) {
                        window.location.href = `product.html?id=${product.product_id}`;
                    }
                });

                // Статус товара
                if (product.is_bestseller || product.is_new) {
                    const status = document.createElement('div');
                    status.className = `status ${product.is_bestseller ? 'hit' : 'new'}`;
                    status.textContent = product.is_bestseller ? 'Хит продаж' : 'Новинка';
                    card.appendChild(status);
                }

                // Изображение
                const img = document.createElement('img');
                img.className = 'product-image';
                img.src = product.image_url;
                img.alt = product.product_name;
                card.appendChild(img);

                // Категория и название
                const category = document.createElement('p');
                category.textContent = product.category_name;
                category.className = 'product-category';
                card.appendChild(category);

                const name = document.createElement('h3');
                name.textContent = product.product_name;
                card.appendChild(name);

                // Рейтинг и отзывы
                const ratingReviews = document.createElement('div');
                ratingReviews.className = 'rating-reviews';

                const rating = document.createElement('span');
                rating.className = 'rating';
                rating.innerHTML = '★'.repeat(product.average_rating) + '☆'.repeat(5 - product.average_rating);

                const reviewBlock = document.createElement('span');
                reviewBlock.className = 'review-block';
                reviewBlock.innerHTML = `
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="reviews">(${product.reviews_count})</span>
                `;

                ratingReviews.append(rating, reviewBlock);
                card.appendChild(ratingReviews);

                // Цена
                const priceContainer = document.createElement('div');
                priceContainer.className = 'price';

                if (product.discount > 0) {
                    const oldPrice = document.createElement('span');
                    oldPrice.style.textDecoration = 'line-through';
                    oldPrice.textContent = `${product.price.toLocaleString('ru-RU')} ₽`;

                    const discountAmount = Math.round(product.price - product.final_price);
                    const discountPercent = Math.round(product.discount);
                    const discount = document.createElement('span');
                    discount.className = 'discount-badge';
                    discount.innerHTML = `
                        <span class="disk_is">-${discountPercent}%</span>
                        <span class="discount-amount">(-${discountAmount.toLocaleString('ru-RU')} ₽)</span>
                    `;

                    const newPrice = document.createElement('div');
                    newPrice.textContent = `${product.final_price.toLocaleString('ru-RU')} ₽`;

                    priceContainer.append(oldPrice, newPrice, discount);
                } else {
                    priceContainer.textContent = `${product.price.toLocaleString('ru-RU')} ₽`;
                }
                card.appendChild(priceContainer);

                // Кнопки
                const actions = document.createElement('div');
                actions.className = 'actions';

                const buyBtn = document.createElement('div');
                buyBtn.className = 'btn-buy';
                buyBtn.textContent = 'Купить в 1 клик';

                const cartBtn = document.createElement('div');
                cartBtn.className = 'btn-cart';
                cartBtn.innerHTML = `
                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.38164 19.7034C8.20955 19.7034 8.88071 19.0394 8.88071 18.2203C8.88071 17.4011 8.20955 16.7371 7.38164 16.7371C6.55372 16.7371 5.88257 17.4011 5.88257 18.2203C5.88257 19.0394 6.55372 19.7034 7.38164 19.7034Z" fill="white"/>
                        <path d="M16.4893 19.7034C17.3173 19.7034 17.9884 19.0394 17.9884 18.2203C17.9884 17.4011 17.3173 16.7371 16.4893 16.7371C15.6614 16.7371 14.9903 17.4011 14.9903 18.2203C14.9903 19.0394 15.6614 19.7034 16.4893 19.7034Z" fill="white"/>
                        <path d="M20.5402 2.13853C20.4782 2.06292 20.4 2.00183 20.3113 1.95966C20.2226 1.91749 20.1256 1.89529 20.0272 1.89464H9.06608C8.61737 1.89464 8.2998 2.33323 8.43984 2.75953C8.52872 3.03009 8.7813 3.21298 9.06608 3.21298H19.1544L17.3755 10.1455H7.38164L4.33685 1.58484C4.30392 1.48363 4.24673 1.3918 4.17016 1.31719C4.09359 1.24259 3.99992 1.18741 3.89713 1.15638L1.16548 0.325828C1.08149 0.300292 0.993233 0.291373 0.905756 0.299582C0.818278 0.307791 0.733291 0.332967 0.655647 0.373671C0.498839 0.455877 0.38146 0.596345 0.329333 0.764174C0.277206 0.932002 0.294601 1.11344 0.377691 1.26858C0.46078 1.42372 0.602759 1.53985 0.772393 1.59143L3.16425 2.31651L6.22236 10.8969L5.1297 11.7802L5.04308 11.8659C4.77281 12.174 4.61961 12.5658 4.60988 12.9737C4.60016 13.3816 4.7345 13.78 4.98978 14.1005C5.17138 14.319 5.40213 14.4924 5.66359 14.6068C5.92505 14.7213 6.20996 14.7736 6.49552 14.7596H17.6153C17.792 14.7596 17.9615 14.6902 18.0864 14.5666C18.2114 14.4429 18.2816 14.2753 18.2816 14.1005C18.2816 13.9256 18.2114 13.758 18.0864 13.6344C17.9615 13.5107 17.792 13.4413 17.6153 13.4413H6.38892C6.3122 13.4387 6.23745 13.4166 6.17189 13.3771C6.10634 13.3375 6.05219 13.282 6.01468 13.2157C5.97717 13.1494 5.95757 13.0747 5.95777 12.9988C5.95797 12.9228 5.97796 12.8482 6.01582 12.7821L7.62149 11.4638H17.9085C18.0625 11.4675 18.213 11.4183 18.3345 11.3246C18.456 11.2308 18.5409 11.0983 18.5747 10.9497L20.6867 2.69883C20.707 2.60056 20.7043 2.49901 20.6789 2.40191C20.6535 2.30482 20.6061 2.21474 20.5402 2.13853Z" fill="white"/>
                    </svg>
                `;

                actions.append(buyBtn, cartBtn);
                card.appendChild(actions);

                // Добавляем карточку в контейнер
                container.appendChild(card);
            });
        }

        window.addEventListener('DOMContentLoaded', () => {
    // Хиты продаж (только из категорий 1 и 5)
    loadProducts({ type: 'hits' }, 'hits-container');
    
    // Новинки (только из категорий 1 и 5)
    loadProducts({ type: 'new' }, 'new-container');
    
    // Готовые ПК (категория 1)
    loadProducts({ category_id: 1 }, 'category-1-container');
    
    // Мыши (категория 5)
    loadProducts({ category_id: 5 }, 'category-5-container');
    loadProducts({ category_id: 3 }, 'category-3-container');
});
    </script>
    <script src="js/slider.js"></script>
</body>
</html>