<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>
    <style>
        /* Основные стили */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        /* Стили статусов */
        .status {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            color: white;
            z-index: 2;
        }

        .status.hit {
            background: #e74c3c;
        }

        .status.new {
            background: #27ae60;
        }

        /* Стили изображения */
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        /* Информация о товаре */
        .product-info {
            padding: 10px 0;
        }

        .product-category {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .product-name {
            font-size: 1.1em;
            margin: 10px 0;
            min-height: 40px;
        }

        /* Стили цены */
        .price-container {
            margin: 15px 0;
        }

        .price {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
        }

        .old-price {
            text-decoration: line-through;
            color: #95a5a6;
            margin-right: 10px;
        }

        .discount {
            color: #e74c3c;
            font-size: 0.9em;
            margin-left: 10px;
        }

        /* Кнопки */
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .btn-buy {
            background: #3498db;
            color: white;
        }

        .btn-cart {
            background: #2ecc71;
            color: white;
        }

        /* Сообщения об ошибках */
        .error-message {
            text-align: center;
            padding: 40px;
            color: #e74c3c;
            font-size: 1.2em;
        }

        .loading {
            text-align: center;
            padding: 40px;
            font-size: 1.2em;
            color: #7f8c8d;
        }
        .search-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .search-input {
        width: 100%;
        padding: 12px 20px;
        border: 2px solid #ddd;
        border-radius: 25px;
        font-size: 1.1em;
        transition: border-color 0.3s;
    }

    .search-input:focus {
        outline: none;
        border-color: #3498db;
    }

    .search-status {
        padding: 10px 0;
        color: #666;
        font-size: 0.9em;
    }
    .filters-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 1px solid #ddd;
        border-radius: 20px;
        background: white;
        cursor: pointer;
        transition: all 0.3s;
    }

    .filter-btn.active {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    .sort-select {
        padding: 8px 16px;
        border: 1px solid #ddd;
        border-radius: 20px;
        background: white;
    }
    .categories-filter {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .categories-list {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .category-item {
        padding: 8px 16px;
        border: 1px solid #ddd;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s;
        background: #f8f9fa;
    }

    .category-item.active {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }
    #resetFilters {
    background: #e74c3c;
    color: white;
    transition: all 0.3s;
}

#resetFilters:hover {
    opacity: 0.9;
}
.rating-filter {
    display: flex;
    align-items: center;
    gap: 10px;
}

.rating-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 20px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
}

.rating-btn.active {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.rating-btn::before {
    content: '★';
    color: #f1c40f;
}
.btn-favorite {
    background: #e67e22;
    color: white;
    transition: all 0.3s;
}

.btn-favorite.added {
    background: #27ae60;
}

.btn-favorite:hover {
    opacity: 0.9;
}
.btn-compare {
    background: #9b59b6;
    color: white;
    transition: all 0.3s;
}

.btn-compare.added {
    background: #2ecc71;
}

.comparison-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 15px;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}
</style>
    </style>
</head>
<body>
<div class="search-container">
    <input 
        type="text" 
        id="searchInput" 
        placeholder="Поиск товаров..." 
        class="search-input"
    >
    <div id="searchStatus" class="search-status"></div>
</div>
<div class="filters-container">
    <div class="filter-group">
        <button class="filter-btn" data-filter="all">Все товары</button>
        <button class="filter-btn" data-filter="hit">Хиты</button>
        <button class="filter-btn" data-filter="new">Новинки</button>
        <button class="filter-btn" id="resetFilters">Сбросить всё</button>
    </div>
    <div class="filter-group">
        <div class="rating-filter">
            <span>Рейтинг:</span>
            <button class="rating-btn" data-rating="4">★★★★ и выше</button>
            <button class="rating-btn" data-rating="3">★★★ и выше</button>
            <button class="rating-btn" data-rating="0">Любой</button>
        </div>
    </div>
    <select class="sort-select" id="sortSelect">
        <option value="">Сортировка</option>
        <option value="price_asc">По возрастанию цены</option>
        <option value="price_desc">По убыванию цены</option>
    </select>
</div>    
<div class="categories-filter">
    <h3>Категории</h3>
    <div class="categories-list" id="categoriesContainer"></div>
</div>
<div class="product-grid" id="productsContainer">
        <div class="loading">Загрузка товаров...</div>
    </div>

    <script>
let currentFilter = 'all';
let currentSort = '';
let selectedCategory = null;
let selectedRating = 0;
let currentUserId = null;
let currentUserFavorites = [];

/**
 * Получает данные текущего пользователя, включая избранное.
 */
async function getCurrentUser() {
    try {
        const response = await fetch('../api/get_full_user.php', {
            credentials: 'include'
        });
        const data = await response.json();
        if (data.status === 'success' && data.data.user) {
            currentUserId = data.data.user.user_id;
            currentUserFavorites = data.data.favorites.map(f => parseInt(f.product_id));
        }
    } catch (error) {
        console.error('Ошибка получения пользователя:', error);
    }
}

/**
 * Загружает товары с учетом текущих фильтров, сортировки и категории.
 */
async function loadProducts() {
    try {
        const params = new URLSearchParams();
        
        if (currentFilter && currentFilter !== 'all') params.append('filter', currentFilter);
        if (currentSort) params.append('sort', currentSort);
        if (selectedCategory) params.append('category', selectedCategory);
        if (selectedRating > 0) params.append('rating', selectedRating);

        const response = await fetch(`http://localhost/brow12/api/products.php?${params}`, {
            credentials: 'include'
        });
        
        if (!response.ok) throw new Error(`Ошибка загрузки: ${response.status}`);
        
        const data = await response.json();
        if (data.status !== 'success') throw new Error(data.message || 'Ошибка данных');
        
        renderProducts(data.data, 'productsContainer');
        
    } catch (error) {
        console.error('Ошибка:', error);
        showErrorMessage(error.message);
    }
}

/**
 * Загружает категории товаров.
 */
async function loadCategories() {
    try {
        const response = await fetch('/brow12/api/categories.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            renderCategories(data.data);
        }
    } catch (error) {
        console.error('Ошибка загрузки категорий:', error);
    }
}

/**
 * Рендерит категории товаров.
 */
function renderCategories(categories) {
    const container = document.getElementById('categoriesContainer');
    container.innerHTML = categories.map(cat => `
        <div class="category-item" 
             data-id="${cat.category_id}"
             data-name="${cat.name}">
            ${cat.name}
        </div>
    `).join('');

    container.querySelectorAll('.category-item').forEach(item => {
        item.addEventListener('click', () => {
            const categoryId = item.dataset.id;
            
            if (selectedCategory === categoryId) {
                selectedCategory = null;
                item.classList.remove('active');
            } else {
                selectedCategory = categoryId;
                container.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
            }
            
            loadProducts();
        });
    });
}

/**
 * Инициализирует фильтры товаров.
 */
function initFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (!btn.id) {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.dataset.filter;
                loadProducts();
            });
        }
    });

    document.getElementById('resetFilters').addEventListener('click', () => {
        currentFilter = 'all';
        currentSort = '';
        selectedCategory = null;
        selectedRating = 0;

        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active');
            if (b.dataset.filter === 'all') b.classList.add('active');
        });
        
        document.getElementById('sortSelect').value = '';
        document.querySelectorAll('.category-item, .rating-btn').forEach(c => c.classList.remove('active'));
        document.getElementById('searchInput').value = '';
        document.getElementById('searchStatus').textContent = '';

        loadProducts();
    });

    document.getElementById('sortSelect').addEventListener('change', (e) => {
        currentSort = e.target.value;
        loadProducts();
    });
}

/**
 * Инициализирует фильтр по рейтингу.
 */
function initRatingFilter() {
    document.querySelectorAll('.rating-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const rating = parseInt(btn.dataset.rating);
            
            if (selectedRating === rating) {
                selectedRating = 0;
                btn.classList.remove('active');
            } else {
                selectedRating = rating;
                document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
            
            loadProducts();
        });
    });
}

/**
 * Рендерит товары в указанном контейнере.
 */
function renderProducts(products, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';

    if (products.length === 0) {
        container.innerHTML = '<div class="error-message">Товары не найдены</div>';
        return;
    }

    products.forEach(product => {
        const card = createProductCard(product);
        container.appendChild(card);
    });
}

/**
 * Создает карточку товара.
 */
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    const productId = parseInt(product.product_id);

    // Бейдж статуса
    if (product.is_new) card.innerHTML += '<div class="status new">Новинка</div>';
    if (product.is_bestseller) card.innerHTML += '<div class="status hit">Хит продаж</div>';

    // Изображение
    const img = document.createElement('img');
    img.className = 'product-image';
    img.src = product.image_url || 'images/placeholder.jpg';
    img.alt = product.name;
    card.appendChild(img);

    // Информация
    const infoContainer = document.createElement('div');
    infoContainer.className = 'product-info';
    infoContainer.innerHTML = `
        <p class="product-category">${product.category_name}</p>
        <h3 class="product-name">${product.name}</h3>
    `;

    // Цена
    const priceContainer = document.createElement('div');
    priceContainer.className = 'price-container';
    const finalPrice = product.discount > 0 
        ? (product.price * (1 - product.discount / 100)).toFixed(2)
        : product.price;
    priceContainer.innerHTML = product.discount > 0 ? `
        <span class="old-price">${product.price.toLocaleString('ru-RU')} ₽</span>
        <span class="price">${finalPrice.toLocaleString('ru-RU')} ₽</span>
        <span class="discount">-${product.discount}%</span>
    ` : `<span class="price">${product.price.toLocaleString('ru-RU')} ₽</span>`;
    infoContainer.appendChild(priceContainer);

    // Кнопки
    const actions = document.createElement('div');
    actions.className = 'actions';
    const isFavorite = currentUserFavorites.includes(productId) || product.is_favorite === 1;
    
    actions.innerHTML = `
        <button class="btn btn-buy">Купить</button>
        <button class="btn btn-cart">В корзину</button>
        <button class="btn btn-favorite ${isFavorite ? 'added' : ''}">
            ${isFavorite ? '★ В избранном' : '❤️ Добавить в избранное'}
        </button>
    `;

    // Обработчик избранного
    const favoriteBtn = actions.querySelector('.btn-favorite');
    favoriteBtn.addEventListener('click', async (e) => {
        e.stopPropagation();
        if (!currentUserId) {
            window.location.href = '/login';
            return;
        }
        
        try {
            const response = await fetch('../api/add_to_favorite.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ product_id: productId }),
                credentials: 'include'
            });
            
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            
            const result = await response.json();
            
            if (result.status === 'success') {
                const index = currentUserFavorites.indexOf(productId);
                
                if (result.action === 'added') {
                    if (index === -1) currentUserFavorites.push(productId);
                    favoriteBtn.classList.add('added');
                    favoriteBtn.textContent = '★ В избранном';
                } else {
                    if (index !== -1) currentUserFavorites.splice(index, 1);
                    favoriteBtn.classList.remove('added');
                    favoriteBtn.textContent = '❤️ Добавить в избранное';
                }
            } else {
                alert(result.message || 'Ошибка при обновлении избранного');
            }
        } catch (error) {
            console.error('Ошибка:', error);
            alert(error.message || 'Ошибка соединения с сервером');
        }
    });

    // Обработчики кнопок "Купить" и "В корзину"
    actions.querySelector('.btn-buy').addEventListener('click', () => handleBuy(productId));
    actions.querySelector('.btn-cart').addEventListener('click', () => handleCart(productId));

    infoContainer.appendChild(actions);
    card.appendChild(infoContainer);

    return card;
}

/**
 * Инициализирует поиск товаров.
 */
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchStatus = document.getElementById('searchStatus');
    let searchTimeout;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        if (query.length < 2) {
            if (query.length === 0) {
                loadProducts();
                searchStatus.textContent = '';
            }
            return;
        }

        searchStatus.textContent = 'Поиск...';
        
        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/brow12/api/search.php?query=${encodeURIComponent(query)}`);
                const data = await response.json();

                if (data.status === 'success') {
                    renderProducts(data.data, 'productsContainer');
                    searchStatus.textContent = `Найдено товаров: ${data.data.length}`;
                } else {
                    searchStatus.textContent = 'Ошибка поиска';
                }
            } catch (error) {
                console.error('Ошибка поиска:', error);
                searchStatus.textContent = 'Ошибка соединения';
            }
        }, 300);
    });
}

/**
 * Показывает сообщение об ошибке.
 */
function showErrorMessage(message) {
    const container = document.getElementById('productsContainer');
    container.innerHTML = `<div class="error-message">Ошибка: ${message}</div>`;
}

/**
 * Обрабатывает нажатие на кнопку "Купить".
 */
function handleBuy(productId) {
    console.log('Покупка товара:', productId);
    // Здесь можно добавить логику для покупки товара
}

/**
 * Обрабатывает нажатие на кнопку "В корзину".
 */
function handleCart(productId) {
    console.log('Добавление в корзину:', productId);
    // Здесь можно добавить логику для добавления товара в корзину
}

/**
 * Инициализация при загрузке страницы.
 */
document.addEventListener('DOMContentLoaded', async () => {
    await getCurrentUser(); // Получаем текущего пользователя
    await loadProducts();
    await loadCategories();
    initSearch();
    initFilters();
    initRatingFilter();
});
</script>
</body>
</html>