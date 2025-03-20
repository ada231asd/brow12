let currentFilters = {
    categories: [],
    priceRange: [0, 0],
    rating: 0,
    characteristics: {},
    searchQuery: '',
    page: 1
};

document.addEventListener('DOMContentLoaded', async () => {
    await initFilters();
    loadProducts();
});

async function initFilters() {
    await loadCategories();
    initPriceSlider();
    setupRatingFilter();
    setupSearch();
}

async function loadCategories() {
    const response = await fetch('/api/categories');
    const categories = await response.json();
    
    const container = document.getElementById('category-filters');
    container.innerHTML = categories.map(cat => `
        <label class="category-filter">
            <input type="checkbox" value="${cat.category_id}" 
                   onchange="toggleCategoryFilter(${cat.category_id})">
            ${cat.name}
        </label>
    `).join('');
}

function initPriceSlider() {
    const slider = document.getElementById('price-slider');
    noUiSlider.create(slider, {
        start: [0, 100000],
        connect: true,
        range: { 'min': 0, 'max': 100000 }
    });

    slider.noUiSlider.on('update', async (values) => {
        currentFilters.priceRange = values.map(Math.round);
        document.getElementById('price-values').textContent = 
            `${currentFilters.priceRange[0]}₽ - ${currentFilters.priceRange[1]}₽`;
        await loadProducts();
    });
}

function setupRatingFilter() {
    document.querySelectorAll('.rating-filter span').forEach(span => {
        span.addEventListener('click', async () => {
            const rating = parseInt(span.dataset.rating);
            currentFilters.rating = currentFilters.rating === rating ? 0 : rating;
            document.querySelectorAll('.rating-filter span').forEach(s => 
                s.classList.toggle('active', parseInt(s.dataset.rating) >= currentFilters.rating)
            );
            await loadProducts();
        });
    });
}

function setupSearch() {
    const searchInput = document.getElementById('search-input');
    let timeout;
    
    searchInput.addEventListener('input', async (e) => {
        clearTimeout(timeout);
        currentFilters.searchQuery = e.target.value.trim();
        timeout = setTimeout(async () => {
            currentFilters.page = 1;
            await loadProducts();
        }, 300);
    });
}

async function loadProducts() {
    try {
        showLoading(true);
        
        const params = new URLSearchParams({
            page: currentFilters.page,
            categories: currentFilters.categories.join(','),
            minPrice: currentFilters.priceRange[0],
            maxPrice: currentFilters.priceRange[1],
            rating: currentFilters.rating,
            search: currentFilters.searchQuery,
            characteristics: JSON.stringify(currentFilters.characteristics)
        });

        const response = await fetch(`../api/products2?${params}`);
        const { products, total } = await response.json();

        renderProducts(products);
        renderPagination(total);
    } catch (error) {
        showError('Ошибка загрузки данных');
    } finally {
        showLoading(false);
    }
}

function renderProducts(products) {
    const container = document.getElementById('products-container');
    container.innerHTML = products.map(product => `
        <div class="product-card">
            ${getProductBadges(product)}
            <img src="${product.image_url}" alt="${product.name}">
            <h3>${product.name}</h3>
            <div class="product-meta">
                ${renderRating(product.rating)}
                <span class="reviews">(${product.reviews_count})</span>
            </div>
            ${renderPrice(product)}
            <div class="product-actions">
                <button class="btn-buy">Купить</button>
                <button class="btn-favorite">♥</button>
            </div>
        </div>
    `).join('');
}

function renderPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / 27);
    const pagination = document.getElementById('pagination');
    
    pagination.innerHTML = `
        <button ${currentFilters.page === 1 ? 'disabled' : ''} onclick="changePage(-1)">Назад</button>
        <span>Страница ${currentFilters.page} из ${totalPages}</span>
        <button ${currentFilters.page === totalPages ? 'disabled' : ''} onclick="changePage(1)">Вперед</button>
    `;
}

function changePage(delta) {
    currentFilters.page += delta;
    loadProducts();
    window.scrollTo(0, 0);
}