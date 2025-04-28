document.addEventListener('DOMContentLoaded', () => {
    let currentFilters = {
        rating: 0,
        category: null,
        filter: null,
        sort: null,
        searchQuery: null,
        page: 1 // Текущая страница (по умолчанию 1)
    };

    const PRODUCTS_PER_PAGE = 20; // Количество продуктов на странице
    let totalProducts = 0; // Общее количество продуктов для расчета страниц
    let searchTimeout = null;
    let abortController = null;
    let allCategories = new Map();

    async function initFilters() {
        await loadAllCategories();
        await loadUserData();
        await loadProducts();
        setupEventListeners();
    }

    function setupEventListeners() {
        setupRatingFilter();
        setupCategoryFilter();
        setupSpecialFilters();
        setupSorting();
        setupResetButton();
        setupSearch();
        setupPagination();
        loadCartState();
    }

    async function loadUserData() {
        try {
            const response = await fetch('../api/get_full_user.php', {
                credentials: 'include'
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                window.userData = {
                    favorites: data.data.favorites || [],
                    comparisons: data.data.comparisons || [],
                    cart: data.data.cart || []
                };
            }
        } catch (error) {
            console.error('Ошибка загрузки данных пользователя:', error);
        }
    }

    // Функция для отправки события обновления счетчиков
    function dispatchCounterUpdateEvent() {
        const cartTotalQuantity = window.userData.cart.reduce((sum, item) => sum + item.quantity, 0);
        const comparisonItems = window.userData.comparisons.length;

        const event = new CustomEvent('updateCounters', {
            detail: {
                cartCount: cartTotalQuantity,
                comparisonCount: comparisonItems
            }
        });
        window.dispatchEvent(event);
    }

    async function loadCartState() {
        try {
            const response = await fetch('../api/get_full_user.php', {
                credentials: 'include'
            });
            
            if (response.status === 401) {
                return;
            }
            
            const data = await response.json();
            
            if (data.status === 'success') {
                window.userData = {
                    favorites: data.data.favorites || [],
                    comparisons: data.data.comparisons || [],
                    cart: data.data.cart || []
                };
                dispatchCounterUpdateEvent();
            } else {
                console.error('Ошибка в ответе:', data.message);
            }
        } catch (error) {
            console.error('Ошибка при загрузке состояния корзины:', error);
        }
    }

    function setupSearch() {
        const searchInput = document.getElementById('searchInput');
        const noResultsBlock = document.getElementById('noResults');
    
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
    
            if (abortController) {
                try { abortController.abort(); } catch (e) { console.warn('Abort error:', e); }
            }
            abortController = new AbortController();
    
            noResultsBlock.style.display = 'none';
    
            if (query === '') {
                currentFilters.searchQuery = null;
                currentFilters.page = 1; // Сбрасываем страницу при очистке поиска
                loadProducts();
                return;
            }
    
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                try {
                    currentFilters.searchQuery = query;
                    currentFilters.page = 1; // Сбрасываем страницу при новом поиске
                    await loadProducts();
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Search error:', error);
                    }
                }
            }, 500);
        });
    }

    async function loadAllCategories() {
        try {
            const response = await fetch(`../api/products.php`);
            const data = await response.json();
            
            if (data.status === 'success') {
                const categoriesMap = new Map();
                data.data.forEach(product => {
                    if (product.category_id && product.category_name) {
                        categoriesMap.set(product.category_id, product.category_name);
                    }
                });
                allCategories = categoriesMap;
                renderCategories();
            }
        } catch (error) {
            console.error('Ошибка загрузки категорий:', error);
        }
    }

    async function loadProducts() {
        try {
            let apiUrl;
            let params;
        
            if (currentFilters.searchQuery) {
                apiUrl = '../api/search.php';
                params = new URLSearchParams({ query: currentFilters.searchQuery });
            } else {
                apiUrl = '../api/products.php';
                params = new URLSearchParams();
                
                if (currentFilters.rating > 0) params.append('rating', currentFilters.rating);
                if (currentFilters.category) params.append('category', currentFilters.category);
                if (currentFilters.filter) params.append('filter', currentFilters.filter);
                if (currentFilters.sort) params.append('sort', currentFilters.sort);
            }
        
            const response = await fetch(`${apiUrl}?${params}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                totalProducts = data.data.length; // Сохраняем общее количество продуктов
                const favoriteIds = window.userData?.favorites?.map(item => item.product_id) || [];
                const comparisonIds = window.userData?.comparisons?.map(item => item.product_id) || [];
                
                // Пагинация: выбираем продукты для текущей страницы
                const startIndex = (currentFilters.page - 1) * PRODUCTS_PER_PAGE;
                const endIndex = startIndex + PRODUCTS_PER_PAGE;
                const paginatedProducts = data.data.slice(startIndex, endIndex);
                
                renderProducts(paginatedProducts, 'products-container', favoriteIds, comparisonIds);
                renderPagination();
            }
        } catch (error) {
            console.error('Ошибка загрузки продуктов:', error);
            document.getElementById('noResults').style.display = 'flex';
        }
    }

    function renderCategories() {
        const container = document.getElementById('categoriesContainer');
        if (!container || !allCategories) return;

        container.innerHTML = Array.from(allCategories).map(([id, name]) => `
            <div class="category-btn ${currentFilters.category == id ? 'active' : ''}" 
                 data-category="${id}">
                ${name}
            </div>
        `).join('');
    }

    function setupRatingFilter() {
        document.querySelectorAll('.rating-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const newRating = parseFloat(this.dataset.rating);
                
                if (currentFilters.rating === newRating) {
                    this.classList.remove('active');
                    currentFilters.rating = 0;
                } else {
                    document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilters.rating = newRating;
                }
                currentFilters.page = 1; // Сбрасываем страницу при изменении фильтра
                loadProducts();
            });
        });
    }

    function setupCategoryFilter() {
        const container = document.getElementById('categoriesContainer');
        container.addEventListener('click', e => {
            const target = e.target.closest('.category-btn');
            if (!target) return;

            const categoryId = target.dataset.category;
            
            if (currentFilters.category === categoryId) {
                target.classList.remove('active');
                currentFilters.category = null;
            } else {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                target.classList.add('active');
                currentFilters.category = categoryId;
            }
            currentFilters.page = 1; // Сбрасываем страницу при изменении категории
            loadProducts();
        });
    }

    function setupSpecialFilters() {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (currentFilters.filter !== this.dataset.filter) {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilters.filter = this.dataset.filter;
                } else {
                    this.classList.remove('active');
                    currentFilters.filter = null;
                }
                currentFilters.page = 1; // Сбрасываем страницу при изменении фильтра
                loadProducts();
            });
        });
    }

    function setupSorting() {
        const sortSelect = document.getElementById('sortSelect');
        if (!sortSelect) return;

        sortSelect.addEventListener('change', function() {
            currentFilters.sort = this.value || null;
            currentFilters.page = 1; // Сбрасываем страницу при изменении сортировки
            loadProducts();
        });
    }

    async function setupResetButton() {
        const resetBtn = document.getElementById('resetFilters');
        resetBtn.addEventListener('click', async () => {
            currentFilters = { 
                rating: 0, 
                category: null, 
                filter: null, 
                sort: null,
                searchQuery: null,
                page: 1 // Сбрасываем страницу
            };
            
            document.querySelectorAll('.active').forEach(el => el.classList.remove('active'));
            document.getElementById('sortSelect').value = '';
            document.getElementById('searchInput').value = '';
            document.getElementById('noResults').style.display = 'none';
            
            await loadUserData();
            await loadProducts();
            dispatchCounterUpdateEvent();
        });
    }

    // Новая функция для настройки пагинации
    function setupPagination() {
        const paginationContainer = document.getElementById('paginationContainer');
        if (!paginationContainer) return;

        paginationContainer.addEventListener('click', (e) => {
            const target = e.target.closest('.page-btn, .prev-btn, .next-btn');
            if (!target) return;

            if (target.classList.contains('prev-btn') && currentFilters.page > 1) {
                currentFilters.page--;
            } else if (target.classList.contains('next-btn') && currentFilters.page < Math.ceil(totalProducts / PRODUCTS_PER_PAGE)) {
                currentFilters.page++;
            } else if (target.classList.contains('page-btn')) {
                currentFilters.page = parseInt(target.dataset.page);
            }

            loadProducts();
        });
    }

    // Новая функция для рендеринга пагинации
    function renderPagination() {
        const paginationContainer = document.getElementById('paginationContainer');
        if (!paginationContainer) return;

        const totalPages = Math.ceil(totalProducts / PRODUCTS_PER_PAGE);
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let paginationHTML = `
            <button class="prev-btn ${currentFilters.page === 1 ? 'disabled' : ''}">←</button>
        `;

        for (let i = 1; i <= totalPages; i++) {
            paginationHTML += `
                <button class="page-btn ${currentFilters.page === i ? 'active' : ''}" data-page="${i}">${i}</button>
            `;
        }

        paginationHTML += `
            <button class="next-btn ${currentFilters.page === totalPages ? 'disabled' : ''}">→</button>
        `;

        paginationContainer.innerHTML = paginationHTML;
    }

    function renderProducts(products, containerId, favoriteProductIds = [], comparisonIds = []) {
        const container = document.getElementById(containerId);
        const noResultsBlock = document.getElementById('noResults');
        if (!container || !noResultsBlock) return;
    
        if (products.length === 0 && currentFilters.searchQuery) {
            container.style.display = 'none';
            noResultsBlock.style.display = 'flex';
        } else {
            container.style.display = 'grid';
            noResultsBlock.style.display = 'none';
        }
    
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
    
        container.innerHTML = products.map(product => `
            <div class="product-card">
                ${product.is_bestseller ? `
                    <div class="status hit">Хит продаж</div>
                ` : product.is_new ? `
                    <div class="status new">Новинка</div>
                ` : `
                    <div class="status no-status"></div>
                `}
                
                <img src="../${product.image_url}" 
                     class="product-image" 
                     alt="${product.name}"
                     onerror="this.src=''">
                
                <div class="name_l"> 
                    <p class="product-category">${product.category_name}</p>            
                    <h3 class="product-title">${product.name}</h3>
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
                                <div class="old-price">${Math.round(product.price || 0).toLocaleString()} ₽</div>
                                <div class="current-price">${Math.round(product.final_price || 0).toLocaleString()} ₽</div>
                                <div class="discount-badge">
                                    <p>-${Math.round(product.discount)}% </p>
                                    <span>- ${Math.round((product.price - product.final_price) || 0).toLocaleString()} ₽</span>
                                </div>
                            ` : `
                                <div class="current-price">${Math.round(product.price || 0).toLocaleString()} ₽</div>
                            `}
                        </div>
                        <div class="but_t">
                            <div class="button favorite-btn ${favoriteProductIds.includes(product.product_id) ? 'active' : ''}" data-product-id="${product.product_id}">
                                <svg width="21" height="18" viewBox="0 0 21 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.1603 2.00017C18.1002 0.937373 16.6951 0.288706 15.1986 0.171335C13.7021 0.0539653 12.213 0.475631 11.0003 1.36017C9.72793 0.413803 8.14427 -0.0153233 6.5682 0.159203C4.99212 0.333729 3.54072 1.09894 2.50625 2.30075C1.47178 3.50256 0.931098 5.05169 0.993077 6.63618C1.05506 8.22067 1.71509 9.72283 2.84028 10.8402L9.05028 17.0602C9.57029 17.5719 10.2707 17.8588 11.0003 17.8588C11.7299 17.8588 12.4303 17.5719 12.9503 17.0602L19.1603 10.8402C20.3279 9.66543 20.9832 8.07644 20.9832 6.42017C20.9832 4.76389 20.3279 3.1749 19.1603 2.00017ZM17.7503 9.46017L11.5403 15.6702C11.4696 15.7415 11.3855 15.7982 11.2928 15.8368C11.2001 15.8755 11.1007 15.8954 11.0003 15.8954C10.8999 15.8954 10.8004 15.8755 10.7077 15.8368C10.615 15.7982 10.5309 15.7415 10.4603 15.6702L4.25028 9.43017C3.46603 8.62851 3.02689 7.55163 3.02689 6.43017C3.02689 5.3087 3.46603 4.23182 4.25028 3.43017C5.04943 2.64115 6.12725 2.19873 7.25028 2.19873C8.3733 2.19873 9.45112 2.64115 10.2503 3.43017C10.3432 3.52389 10.4538 3.59829 10.5757 3.64906C10.6976 3.69983 10.8283 3.72596 10.9603 3.72596C11.0923 3.72596 11.223 3.69983 11.3449 3.64906C11.4667 3.59829 11.5773 3.52389 11.6703 3.43017C12.4694 2.64115 13.5472 2.19873 14.6703 2.19873C15.7933 2.19873 16.8711 2.64115 17.6703 3.43017C18.4653 4.22132 18.9189 5.29236 18.9338 6.41385C18.9488 7.53535 18.5239 8.6181 17.7503 9.43017V9.46017Z" fill="${favoriteProductIds.includes(product.product_id) ? '#8A33FD' : '#C8CACB'}"/>
                                </svg>
                            </div>
                            <div class="button comparison-btn ${comparisonIds.includes(product.product_id) ? 'active' : ''}" data-product-id="${product.product_id}">
                                <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 10C0.734784 10 0.48043 10.1054 0.292893 10.2929C0.105357 10.4804 0 10.7348 0 11V19C0 19.2652 0.105357 19.5196 0.292893 19.7071C0.48043 19.8946 0.734784 20 1 20C1.26522 20 1.51957 19.8946 1.70711 19.7071C1.89464 19.5196 2 19.2652 2 19V11C2 10.7348 1.89464 10.4804 1.70711 10.2929C1.51957 10.1054 1.26522 10 1 10ZM6 0C5.73478 0 5.48043 0.105357 5.29289 0.292893C5.10536 0.48043 5 0.734784 5 1V19C5 19.2652 5.10536 19.5196 5.29289 19.7071C5.48043 19.8946 5.73478 20 6 20C6.26522 20 6.51957 19.8946 6.70711 19.7071C6.89464 19.5196 7 19.2652 7 19V1C7 0.734784 6.89464 0.48043 6.70711 0.292893C6.51957 0.105357 6.26522 0 6 0ZM16 14C15.7348 14 15.4804 14.1054 15.2929 14.2929C15.1054 14.4804 15 14.7348 15 15V19C15 19.2652 15.1054 19.5196 15.2929 19.7071C15.4804 19.8946 15.7348 20 16 20C16.2652 20 16.5196 19.8946 16.7071 19.7071C16.8946 19.2652 17 19.2652 17 19V15C17 14.7348 16.8946 14.4804 16.7071 14.2929C16.5196 14.1054 16.2652 14 16 14ZM11 6C10.7348 6 10.4804 6.10536 10.2929 6.29289C10.1054 6.48043 10 6.73478 10 7V19C10 19.2652 10.1054 19.5196 10.2929 19.7071C10.4804 19.8946 10.7348 20 11 20C11.2652 20 11.5196 19.8946 11.7071 19.7071C11.8946 19.5196 12 19.2652 12 19V7C12 6.73478 11.8946 6.48043 11.7071 6.29289C11.5196 6.10536 11.2652 6 11 6Z" fill="${comparisonIds.includes(product.product_id) ? '#8A33FD' : '#C8CACB'}"/>
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
                                <path d="M20.5402 2.13853C20.4782 2.06292 20.4 2.00183 20.3113 1.95966C20.2226 1.91749 20.1256 1.89529 20.0272 1.89464H9.06608C8.61737 1.89464 8.2998 2.33323 8.43984 2.75953C8.52872 3.03009 8.7813 3.21298 9.06608 3.21298H19.1544L17.3755 10.1455H7.38164L4.33685 1.58484C4.30392 1.48363 4.24673 1.3918 4.17016 1.31719C4.09359 1.24259 3.99992 1.18741 3.89713 1.15638L1.16548 0.325828C1.08149 0.300292 0.993233 0.291373 0.905756 0.299582C0.818278 0.307791 0.733291 0.332967 0.655647 0.373671C0.498839 0.455877 0.38146 0.596345 0.329333 0.764174C0.277206 0.932002 0.294601 1.11344 0.377691 1.26858C0.46078 1.42372 0.602759 1.53985 0.772393 1.59143L3.16425 2.3165L6.22236 10.8969L5.1297 11.7802L5.04308 11.8659C4.77281 12.174 4.61961 12.5658 4.60988 12.9737C4.60016 13.3816 4.7345 13.78 4.98978 14.1005C5.17138 14.319 5.40213 14.4924 5.66359 14.6068C5.92505 14.7213 6.20996 14.7736 6.49552 14.7596H17.6153C17.792 14.7596 17.9615 14.6902 18.0864 14.5666C18.2114 14.4429 18.2816 14.2753 18.2816 14.1005C18.2816 13.9256 18.2114 13.758 18.0864 13.6344C17.9615 13.5107 17.792 13.4413 17.6153 13.4413H6.38892C6.3122 13.4387 6.23745 13.4166 6.17189 13.3771C6.10634 13.3375 6.05219 13.282 6.01468 13.2157C5.97717 13.1494 5.95757 13.0747 5.95777 12.9988C5.95797 12.9228 5.97796 12.8482 6.01582 12.7821L7.62149 11.4638H17.9085C18.0625 11.4675 18.213 11.4183 18.3345 11.3246C18.456 11.2308 18.5409 11.0983 18.5747 10.9497L20.6867 2.69883C20.707 2.60056 20.7043 2.49901 20.6789 2.40191C20.6535 2.30482 20.6061 2.21474 20.5402 2.13853Z" fill="white"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    
        container.querySelectorAll('.btn-buy').forEach(btn => {
            btn.addEventListener('click', (e) => e.stopPropagation());
        });
    
        container.querySelectorAll('.btn-cart').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const productCard = e.target.closest('.product-card');
                const comparisonBtn = productCard.querySelector('.comparison-btn');
                const productId = comparisonBtn.dataset.productId;
                
                if (!productId) {
                    console.error('Product ID not found');
                    return;
                }
                
                try {
                    const addResponse = await fetch('../api/add_to_cart.php', {
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
                        dispatchCounterUpdateEvent();
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
    
        container.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = btn.dataset.productId;
                
                try {
                    const response = await fetch('../api/add_to_favorite.php', {
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
                        dispatchCounterUpdateEvent();
                    }
                } catch (error) {
                    console.error('Ошибка при добавлении в избранное:', error);
                    showNotification('Произошла ошибка', 'error');
                }
            });
        });
    
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
                    await fetch('../api/add_to_comparison.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify({ product_id: productId })
                    });
        
                    await loadUserData();
                    dispatchCounterUpdateEvent();
        
                    const isActuallyCompared = window.userData.comparisons.some(
                        item => item.product_id == productId
                    );
        
                    if (isActuallyCompared !== !wasActive) {
                        btn.classList.toggle('active');
                        if (icon) {
                            icon.style.fill = isActuallyCompared ? '#8A33FD' : '#C8CACB';
                        }
                    }
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
    
        container.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target === card || card.contains(e.target)) {
                    const isButtonClick = e.target.closest('.btn-buy, .btn-cart, .favorite-btn, .comparison-btn');
                    if (!isButtonClick) {
                        const productId = card.querySelector('.favorite-btn').dataset.productId;
                        // Передаем текущий URL как параметр referrer
                        const currentUrl = encodeURIComponent(window.location.href);
                        window.location.href = `../product.php?id=${productId}&referrer=${currentUrl}`;
                    }
                }
            });
        });
    }

    function animateCartButton(button) {
        button.classList.add('animate');
        setTimeout(() => {
            button.classList.remove('animate');
        }, 1000);
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    initFilters();
});