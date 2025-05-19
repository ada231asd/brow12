<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DimTech</title>
    <link rel="stylesheet" href="/brow12/css/style.css">
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
        <a href="front/news.php">Подробней ></a>
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
function renderProducts(products, containerId, favoriteProductIds = [], comparisonProductIds = []) {
    const container = document.getElementById(containerId);
    container.innerHTML = ''; // Очистка контейнера перед добавлением новых товаров

    // Функция для рендеринга звезд рейтинга
    const renderStars = (rating) => {
        const numericRating = parseFloat(rating) || 0;
        const clampedRating = Math.min(Math.max(numericRating, 0), 5);
        const fullStars = Math.floor(clampedRating);
        const hasHalfStar = clampedRating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

        return `
            ${'<div class="star full"></div>'.repeat(fullStars)}
            ${hasHalfStar ? '<div class="star half"></div>' : ''}
            ${'<div class="star empty"></div>'.repeat(emptyStars)}
        `;
    };

    products.forEach(product => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.dataset.productId = product.product_id; // Добавляем ID продукта для навигации

        // HTML структура карточки товара
        card.innerHTML = `
            ${product.is_bestseller ? `
                <div class="status hit">Хит продаж</div>
            ` : product.is_new ? `
                <div class="status new">Новинка</div>
            ` : `
                <div class="status no-status"></div>
            `}
            
            <img src="${product.image_url}" 
                 class="product-image" 
                 alt="${product.product_name}"
                 onerror="this.src=''">
            
            <div class="name_l"> 
                <p class="product-category">${product.category_name}</p>            
                <h3 class="product-title">${product.product_name}</h3>
            </div>
            
            <div class="blok_inf">
                <div class="rating-block">
                    <div class="stars">${renderStars(product.average_rating)}</div>
                    <div class="reviews">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>(${product.reviews_count || 0})</span>
                    </div>
                </div>
                <div class="ppt">
                    <div class="price-block ${product.discount > 0 ? 'with-discount' : 'no-discount'}">
                        ${product.discount > 0 ? `
                            <div class="old-price">${Math.round(product.price).toLocaleString('ru-RU')} ₽</div>
                            <div class="current-price">${Math.round(product.final_price).toLocaleString('ru-RU')} ₽</div>
                            <div class="discount-badge">
                                <p>-${Math.round(product.discount)}%</p>
                                <span>-${Math.round(product.price - product.final_price).toLocaleString('ru-RU')} ₽</span>
                            </div>
                        ` : `
                            <div class="current-price">${Math.round(product.price).toLocaleString('ru-RU')} ₽</div>
                        `}
                    </div>
                    <div class="but_t">
                        <div class="button favorite-btn ${favoriteProductIds.includes(product.product_id) ? 'active' : ''}" data-product-id="${product.product_id}">
                            <svg width="21" height="18" viewBox="0 0 21 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.1603 2.00017C18.1002 0.937373 16.6951 0.288706 15.1986 0.171335C13.7021 0.0539653 12.213 0.475631 11.0003 1.36017C9.72793 0.413803 8.14427 -0.0153233 6.5682 0.159203C4.99212 0.333729 3.54072 1.09894 2.50625 2.30075C1.47178 3.50256 0.931098 5.05169 0.993077 6.63618C1.05506 8.22067 1.71509 9.72283 2.84028 10.8402L9.05028 17.0602C9.57029 17.5719 10.2707 17.8588 11.0003 17.8588C11.7299 17.8588 12.4303 17.5719 12.9503 17.0602L19.1603 10.8402C20.3279 9.66543 20.9832 8.07644 20.9832 6.42017C20.9832 4.76389 20.3279 3.1749 19.1603 2.00017ZM17.7503 9.46017L11.5403 15.6702C11.4696 15.7415 11.3855 15.7982 11.2928 15.8368C11.2001 15.8755 11.1007 15.8954 11.0003 15.8954C10.8999 15.8954 10.8004 15.8755 10.7077 15.8368C10.615 15.7982 10.5309 15.7415 10.4603 15.6702L4.25028 9.43017C3.46603 8.62851 3.02689 7.55163 3.02689 6.43017C3.02689 5.3087 3.46603 4.23182 4.25028 3.43017C5.04943 2.64115 6.12725 2.19873 7.25028 2.19873C8.3733 2.19873 9.45112 2.64115 10.2503 3.43017C10.3432 3.52389 10.4538 3.59829 10.5757 3.64906C10.6976 3.69983 10.8283 3.72596 10.9603 3.72596C11.0923 3.72596 11.223 3.69983 11.3449 3.64906C11.4667 3.59829 11.5773 3.52389 11.6703 3.43017C12.4694 2.64115 13.5472 2.19873 14.6703 2.19873C15.7933 2.19873 16.8711 2.64115 17.6703 3.43017C18.4653 4.22132 18.9189 5.29236 18.9338 6.41385C18.9488 7.53535 18.5239 8.6181 17.7503 9.43017V9.46017Z" fill="${favoriteProductIds.includes(product.product_id) ? '#8A33FD' : '#C8CACB'}"/>
                            </svg>
                        </div>
                        <div class="button comparison-btn ${comparisonProductIds.includes(product.product_id) ? 'active' : ''}" data-product-id="${product.product_id}">
                            <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 10C0.734784 10 0.48043 10.1054 0.292893 10.2929C0.105357 10.4804 0 10.7348 0 11V19C0 19.2652 0.105357 19.5196 0.292893 19.7071C0.48043 19.8946 0.734784 20 1 20C1.26522 20 1.51957 19.8946 1.70711 19.7071C1.89464 19.5196 2 19.2652 2 19V11C2 10.7348 1.89464 10.4804 1.70711 10.2929C1.51957 10.1054 1.26522 10 1 10ZM6 0C5.73478 0 5.48043 0.105357 5.29289 0.292893C5.10536 0.48043 5 0.734784 5 1V19C5 19.2652 5.10536 19.5196 5.29289 19.7071C5.48043 19.8946 5.73478 20 6 20C6.26522 20 6.51957 19.8946 6.70711 19.7071C6.89464 19.5196 7 19.2652 7 19V1C7 0.734784 6.89464 0.48043 6.70711 0.292893C6.51957 0.105357 6.26522 0 6 0ZM16 14C15.7348 14 15.4804 14.1054 15.2929 14.2929C15.1054 14.4804 15 14.7348 15 15V19C15 19.2652 15.1054 19.5196 15.2929 19.7071C15.4804 19.8946 15.7348 20 16 20C16.2652 20 16.5196 19.8946 16.7071 19.7071C16.8946 19.2652 17 19.2652 17 19V15C17 14.7348 16.8946 14.4804 16.7071 14.2929C16.5196 14.1054 16.2652 14 16 14ZM11 6C10.7348 6 10.4804 6.10536 10.2929 6.29289C10.1054 6.48043 10 6.73478 10 7V19C10 19.2652 10.1054 19.5196 10.2929 19.7071C10.4804 19.8946 10.7348 20 11 20C11.2652 20 11.5196 19.8946 11.7071 19.7071C11.8946 19.5196 12 19.2652 12 19V7C12 6.73478 11.8946 6.48043 11.7071 6.29289C11.5196 6.10536 11.2652 6 11 6Z" fill="${comparisonProductIds.includes(product.product_id) ? '#8A33FD' : '#C8CACB'}"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="product-actions">
                    <button class="btn-buy">Купить в 1 клик</button>
                    <button class="btn-cart">
                        <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.38164 19.7034C8.20955 19.7034 8.88071 19.0394 8.88071 18.2203C8.88071 17.4011 8.20955 16.7371 7.38164 16.7371C6.55372 16.7371 5.88257 17.4011 5.88257 18.2203C5.88257 19.0394 6.55372 19.7034 7.38164 19.7034Z" fill="white"/>
                            <path d="M16.4893 19.7034C17.3173 19.7034 17.9884 19.0394 17.9884 18.2203C17.9884 17.4011 17.3173 16.7371 16.4893 16.7371C15.6614 16.7371 14.9903 17.4011 14.9903 18.2203C14.9903 19.0394 15.6614 19.7034 16.4893 19.7034Z" fill="white"/>
                            <path d="M20.5402 2.13853C20.4782 2.06292 20.4 2.00183 20.3113 1.95966C20.2226 1.91749 20.1256 1.89529 20.0272 1.89464H9.06608C8.61737 1.89464 8.2998 2.33323 8.43984 2.75953C8.52872 3.03009 8.7813 3.21298 9.06608 3.21298H19.1544L17.3755 10.1455H7.38164L4.33685 1.58484C4.30392 1.48363 4.24673 1.3918 4.17016 1.31719C4.09359 1.24259 3.99992 1.18741 3.89713 1.15638L1.16548 0.325828C1.08149 0.300292 0.993233 0.291373 0.905756 0.299582C0.818278 0.307791 0.733291 0.332967 0.655647 0.373671C0.498839 0.455877 0.38146 0.596345 0.329333 0.764174C0.277206 0.932002 0.294601 1.11344 0.377691 1.26858C0.46078 1.42372 0.602759 1.53985 0.772393 1.59143L3.16425 2.31651L6.22236 10.8969L5.1297 11.7802L5.04308 11.8659C4.77281 12.174 4.61961 12.5658 4.60988 12.9737C4.60016 13.3816 4.7345 13.78 4.98978 14.1005C5.17138 14.319 5.40213 14.4924 5.66359 14.6068C5.92505 14.7213 6.20996 14.7736 6.49552 14.7596H17.6153C17.792 14.7596 17.9615 14.6902 18.0864 14.5666C18.2114 14.4429 18.2816 14.2753 18.2816 14.1005C18.2816 13.9256 18.2114 13.758 18.0864 13.6344C17.9615 13.5107 17.792 13.4413 17.6153 13.4413H6.38892C6.3122 13.4387 6.23745 13.4166 6.17189 13.3771C6.10634 13.3375 6.05219 13.282 6.01468 13.2157C5.97717 13.1494 5.95757 13.0747 5.95777 12.9988C5.95797 12.9228 5.97796 12.8482 6.01582 12.7821L7.62149 11.4638H17.9085C18.0625 11.4675 18.213 11.4183 18.3345 11.3246C18.456 11.2308 18.5409 11.0983 18.5747 10.9497L20.6867 2.69883C20.707 2.60056 20.7043 2.49901 20.6789 2.40191C20.6535 2.30482 20.6061 2.21474 20.5402 2.13853Z" fill="white"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(card);
    });

    // Добавляем обработчики событий после рендеринга
    setupEventListeners(container);
}

// Функция для показа уведомлений
function showNotification(message, type = 'success') {
    console.log(`${type}: ${message}`);
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Функция для анимации кнопки корзины
function animateCartButton(button) {
    button.classList.add('animate');
    setTimeout(() => {
        button.classList.remove('animate');
    }, 1000);
}

// Функция для загрузки данных пользователя
async function loadUserData() {
    try {
        const response = await fetch('/../brow12/api/get_full_user.php', {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            window.userData = {
                favorites: data.data.favorites || [],
                comparisons: data.data.comparisons || [],
                cart: data.data.cart || []
            };
            return window.userData;
        }
    } catch (error) {
        console.error('Ошибка загрузки данных пользователя:', error);
    }
    return { favorites: [], comparisons: [], cart: [] };
}

// Функция для настройки обработчиков событий
function setupEventListeners(container) {
    // Обработчики для кнопок покупки и корзины
    container.querySelectorAll('.btn-buy').forEach(btn => {
        btn.addEventListener('click', (e) => e.stopPropagation());
    });

    container.querySelectorAll('.btn-cart').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const productCard = btn.closest('.product-card');
            const comparisonBtn = productCard.querySelector('.comparison-btn');
            const productId = comparisonBtn.dataset.productId;
            
            if (!productId) {
                console.error('Product ID not found');
                return;
            }
            
            try {
                const addResponse = await fetch('/../brow12/api/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify({ product_id: productId })
                });
                
                const addResult = await addResponse.json();
                
                if (addResult.status === 'success') {
                    await loadUserData();
                    showNotification(addResult.message);
                    animateCartButton(btn);
                } else {
                    showNotification(addResult.message, 'error');
                }
            } catch (error) {
                console.error('Ошибка при работе с корзиной:', error);
                showNotification('Произошла ошибка', 'error');
            }
        });
    });

    // Обработчики для кнопок избранного
    container.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = btn.dataset.productId;
            
            try {
                const response = await fetch('/../brow12/api/add_to_favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify({ product_id: productId })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    btn.classList.toggle('active');
                    const icon = btn.querySelector('svg path');
                    icon.setAttribute('fill', btn.classList.contains('active') ? '#8A33FD' : '#C8CACB');
                    await loadUserData();
                    showNotification(result.message);
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Ошибка при добавлении в избранное:', error);
                showNotification('Произошла ошибка', 'error');
            }
        });
    });

    // Обработчики для кнопок сравнения
    container.querySelectorAll('.comparison-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = btn.dataset.productId;
            const wasActive = btn.classList.contains('active');
    
            btn.classList.toggle('active', !wasActive);
            const icon = btn.querySelector('svg path');
            if (icon) {
                icon.style.fill = !wasActive ? '#8A33FD' : '#C8CACB';
            }
    
            try {
                await fetch('/../brow12/api/add_to_comparison.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ product_id: productId })
                });
    
                await loadUserData();
    
                const isActuallyCompared = window.userData.comparisons.some(
                    item => item.product_id == productId
                );
    
                if (isActuallyCompared !== !wasActive) {
                    btn.classList.toggle('active');
                    if (icon) {
                        icon.style.fill = isActuallyCompared ? '#8A33FD' : '#C8CACB';
                    }
                }
    
                showNotification(isActuallyCompared ? 'Добавлено в сравнение' : 'Удалено из сравнения');
            } catch (error) {
                console.error('Ошибка:', error);
                btn.classList.toggle('active', wasActive);
                if (icon) {
                    icon.style.fill = wasActive ? '#8A33FD' : '#C8CACB';
                }
                showNotification('Произошла ошибка', 'error');
            }
        });
    });

    // Обработчик клика по карточке
    container.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', (e) => {
            const isButtonClick = e.target.closest('.btn-buy, .btn-cart, .favorite-btn, .comparison-btn');
            if (!isButtonClick) {
                const productId = card.dataset.productId;
                window.location.href = `product.php?id=${productId}`;
            }
        });
    });
}

window.addEventListener('DOMContentLoaded', async () => {
    // Загружаем данные пользователя
    const userData = await loadUserData();
    const favoriteIds = userData.favorites.map(item => item.product_id);
    const comparisonIds = userData.comparisons.map(item => item.product_id);

    // Хиты продаж
    loadProducts({ type: 'hits' }, 'hits-container', favoriteIds, comparisonIds);
    
    // Новинки
    loadProducts({ type: 'new' }, 'new-container', favoriteIds, comparisonIds);
    
    // Готовые ПК (категория 1)
    loadProducts({ category_id: 1 }, 'category-1-container', favoriteIds, comparisonIds);
    
    // Мыши (категория 5)
    loadProducts({ category_id: 5 }, 'category-5-container', favoriteIds, comparisonIds);
    
    // Процессоры (категория 3)
    loadProducts({ category_id: 3 }, 'category-3-container', favoriteIds, comparisonIds);
});

// Модифицированная функция loadProducts для поддержки favoriteIds и comparisonIds
async function loadProducts(params, containerId, favoriteIds = [], comparisonIds = []) {
    try {
        const url = new URL('api/products_nn.php', window.location.href);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.status === 'success') {
            renderProducts(data.data, containerId, favoriteIds, comparisonIds);
        } else {
            console.error('Ошибка загрузки:', data.message);
            showNotification('Ошибка загрузки продуктов', 'error');
        }
    } catch (error) {
        console.error('Ошибка загрузки:', error);
        showNotification('Произошла ошибка при загрузке', 'error');
    }
}
    </script>
    <script src="js/slider.js"></script>
</body>
</html>