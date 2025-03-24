document.addEventListener('DOMContentLoaded', () => {
    let currentFilters = {
        rating: 0,
        category: null,
        filter: null,
        sort: null,
        searchQuery: null
    };

    let searchTimeout = null;
    let abortController = null;
    let allCategories = new Map();

    async function initFilters() {
        await loadAllCategories();
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
    }

    function setupSearch() {
        const searchInput = document.getElementById('searchInput');
        const searchStatus = document.getElementById('searchStatus');
        const noResultsBlock = document.getElementById('noResults');

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            if (abortController) abortController.abort();
            abortController = new AbortController();

            searchStatus.textContent = '';
            noResultsBlock.style.display = 'none';

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                try {
                    currentFilters.searchQuery = query || null;
                    await loadProducts();
                    
                    if (query.length > 0) {
                        const resultsCount = document.getElementById('products-container').children.length;
                        searchStatus.textContent = resultsCount > 0 
                            ? `Найдено: ${resultsCount}` 
                            : 'Ничего не найдено';
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        searchStatus.textContent = 'Ошибка поиска';
                        console.error('Search error:', error);
                    }
                }
            }, 300);
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
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            
            const data = await response.json();
            
            if (data.status === 'success') {
                renderProducts(data.data, 'products-container');
            }
        } catch (error) {
            console.error('Ошибка загрузки продуктов:', error);
            document.getElementById('products-container').innerHTML = 
                '<div class="error">Ошибка загрузки товаров</div>';
            document.getElementById('noResults').style.display = 'none';
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
                
                if(currentFilters.rating === newRating) {
                    this.classList.remove('active');
                    currentFilters.rating = 0;
                } else {
                    document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilters.rating = newRating;
                }
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
            
            if(currentFilters.category === categoryId) {
                target.classList.remove('active');
                currentFilters.category = null;
            } else {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                target.classList.add('active');
                currentFilters.category = categoryId;
            }
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
                loadProducts();
            });
        });
    }

    function setupSorting() {
        const sortSelect = document.getElementById('sortSelect');
        if (!sortSelect) return;

        sortSelect.addEventListener('change', function() {
            currentFilters.sort = this.value || null;
            loadProducts();
        });
    }

    function setupResetButton() {
        const resetBtn = document.getElementById('resetFilters');
        resetBtn.addEventListener('click', () => {
            currentFilters = { 
                rating: 0, 
                category: null, 
                filter: null, 
                sort: null,
                searchQuery: null
            };
            
            document.querySelectorAll('.active').forEach(el => el.classList.remove('active'));
            document.getElementById('sortSelect').value = '';
            document.getElementById('searchInput').value = '';
            document.getElementById('searchStatus').textContent = '';
            document.getElementById('noResults').style.display = 'none';
            loadProducts();
        });
    }

    function renderProducts(products, containerId) {
        const container = document.getElementById(containerId);
        const noResultsBlock = document.getElementById('noResults');
        
        if (!container || !noResultsBlock) return;

        // Управление отображением результатов
        if (products.length === 0 && currentFilters.searchQuery) {
            container.style.display = 'none';
            noResultsBlock.style.display = 'flex';
        } else {
            container.style.display = 'grid';
            noResultsBlock.style.display = 'none';
        }

        // Рендер карточек товаров
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
                
                <img src="${product.image_url}" 
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
                            <div class="button">
                                <svg width="21" height="18" viewBox="0 0 21 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.1603 2.00017C18.1002 0.937373 16.6951 0.288706 15.1986 0.171335C13.7021 0.0539653 12.213 0.475631 11.0003 1.36017C9.72793 0.413803 8.14427 -0.0153233 6.5682 0.159203C4.99212 0.333729 3.54072 1.09894 2.50625 2.30075C1.47178 3.50256 0.931098 5.05169 0.993077 6.63618C1.05506 8.22067 1.71509 9.72283 2.84028 10.8402L9.05028 17.0602C9.57029 17.5719 10.2707 17.8588 11.0003 17.8588C11.7299 17.8588 12.4303 17.5719 12.9503 17.0602L19.1603 10.8402C20.3279 9.66543 20.9832 8.07644 20.9832 6.42017C20.9832 4.76389 20.3279 3.1749 19.1603 2.00017ZM17.7503 9.46017L11.5403 15.6702C11.4696 15.7415 11.3855 15.7982 11.2928 15.8368C11.2001 15.8755 11.1007 15.8954 11.0003 15.8954C10.8999 15.8954 10.8004 15.8755 10.7077 15.8368C10.615 15.7982 10.5309 15.7415 10.4603 15.6702L4.25028 9.43017C3.46603 8.62851 3.02689 7.55163 3.02689 6.43017C3.02689 5.3087 3.46603 4.23182 4.25028 3.43017C5.04943 2.64115 6.12725 2.19873 7.25028 2.19873C8.3733 2.19873 9.45112 2.64115 10.2503 3.43017C10.3432 3.52389 10.4538 3.59829 10.5757 3.64906C10.6976 3.69983 10.8283 3.72596 10.9603 3.72596C11.0923 3.72596 11.223 3.69983 11.3449 3.64906C11.4667 3.59829 11.5773 3.52389 11.6703 3.43017C12.4694 2.64115 13.5472 2.19873 14.6703 2.19873C15.7933 2.19873 16.8711 2.64115 17.6703 3.43017C18.4653 4.22132 18.9189 5.29236 18.9338 6.41385C18.9488 7.53535 18.5239 8.6181 17.7503 9.43017V9.46017Z" fill="#C8CACB"/>
                                </svg>
                            </div>
                            <div class="button">
                                <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 10C0.734784 10 0.48043 10.1054 0.292893 10.2929C0.105357 10.4804 0 10.7348 0 11V19C0 19.2652 0.105357 19.5196 0.292893 19.7071C0.48043 19.8946 0.734784 20 1 20C1.26522 20 1.51957 19.8946 1.70711 19.7071C1.89464 19.5196 2 19.2652 2 19V11C2 10.7348 1.89464 10.4804 1.70711 10.2929C1.51957 10.1054 1.26522 10 1 10ZM6 0C5.73478 0 5.48043 0.105357 5.29289 0.292893C5.10536 0.48043 5 0.734784 5 1V19C5 19.2652 5.10536 19.5196 5.29289 19.7071C5.48043 19.8946 5.73478 20 6 20C6.26522 20 6.51957 19.8946 6.70711 19.7071C6.89464 19.5196 7 19.2652 7 19V1C7 0.734784 6.89464 0.48043 6.70711 0.292893C6.51957 0.105357 6.26522 0 6 0ZM16 14C15.7348 14 15.4804 14.1054 15.2929 14.2929C15.1054 14.4804 15 14.7348 15 15V19C15 19.2652 15.1054 19.5196 15.2929 19.7071C15.4804 19.8946 15.7348 20 16 20C16.2652 20 16.5196 19.8946 16.7071 19.7071C16.8946 19.5196 17 19.2652 17 19V15C17 14.7348 16.8946 14.4804 16.7071 14.2929C16.5196 14.1054 16.2652 14 16 14ZM11 6C10.7348 6 10.4804 6.10536 10.2929 6.29289C10.1054 6.48043 10 6.73478 10 7V19C10 19.2652 10.1054 19.5196 10.2929 19.7071C10.4804 19.8946 10.7348 20 11 20C11.2652 20 11.5196 19.8946 11.7071 19.7071C11.8946 19.5196 12 19.2652 12 19V7C12 6.73478 11.8946 6.48043 11.7071 6.29289C11.5196 6.10536 11.2652 6 11 6Z" fill="#C8CACB"/>
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
                                <path d="M20.5402 2.13853C20.4782 2.06292 20.4 2.00183 20.3113 1.95966C20.2226 1.91749 20.1256 1.89529 20.0272 1.89464H9.06608C8.61737 1.89464 8.2998 2.33323 8.43984 2.75953C8.52872 3.03009 8.7813 3.21298 9.06608 3.21298H19.1544L17.3755 10.1455H7.38164L4.33685 1.58484C4.30392 1.48363 4.24673 1.3918 4.17016 1.31719C4.09359 1.24259 3.99992 1.18741 3.89713 1.15638L1.16548 0.325828C1.08149 0.300292 0.993233 0.291373 0.905756 0.299582C0.818278 0.307791 0.733291 0.332967 0.655647 0.373671C0.498839 0.455877 0.38146 0.596345 0.329333 0.764174C0.277206 0.932002 0.294601 1.11344 0.377691 1.26858C0.46078 1.42372 0.602759 1.53985 0.772393 1.59143L3.16425 2.31651L6.22236 10.8969L5.1297 11.7802L5.04308 11.8659C4.77281 12.174 4.61961 12.5658 4.60988 12.9737C4.60016 13.3816 4.7345 13.78 4.98978 14.1005C5.17138 14.319 5.40213 14.4924 5.66359 14.6068C5.92505 14.7213 6.20996 14.7736 6.49552 14.7596H17.6153C17.792 14.7596 17.9615 14.6902 18.0864 14.5666C18.2114 14.4429 18.2816 14.2753 18.2816 14.1005C18.2816 13.9256 18.2114 13.758 18.0864 13.6344C17.9615 13.5107 17.792 13.4413 17.6153 13.4413H6.38892C6.3122 13.4387 6.23745 13.4166 6.17189 13.3771C6.10634 13.3375 6.05219 13.282 6.01468 13.2157C5.97717 13.1494 5.95757 13.0747 5.95777 12.9988C5.95797 12.9228 5.97796 12.8482 6.01582 12.7821L7.62149 11.4638H17.9085C18.0625 11.4675 18.213 11.4183 18.3345 11.3246C18.456 11.2308 18.5409 11.0983 18.5747 10.9497L20.6867 2.69883C20.707 2.60056 20.7043 2.49901 20.6789 2.40191C20.6535 2.30482 20.6061 2.21474 20.5402 2.13853Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M20.0272 1.89464C20.1256 1.89529 20.2226 1.91749 20.3113 1.95966C20.4 2.00183 20.4782 2.06292 20.5402 2.13853C20.6061 2.21474 20.6535 2.30482 20.6789 2.40191C20.7043 2.49901 20.707 2.60056 20.6867 2.69883L18.5747 10.9497C18.5409 11.0983 18.456 11.2308 18.3345 11.3246C18.213 11.4183 18.0625 11.4675 17.9085 11.4638H7.62149L6.01582 12.7821C5.97796 12.8482 5.95797 12.9228 5.95777 12.9988C5.95757 13.0747 5.97717 13.1494 6.01468 13.2157C6.05219 13.282 6.10634 13.3375 6.17189 13.3771C6.23745 13.4166 6.3122 13.4387 6.38892 13.4413H17.6153C17.792 13.4413 17.9615 13.5107 18.0864 13.6344C18.2114 13.758 18.2816 13.9256 18.2816 14.1005C18.2816 14.2753 18.2114 14.4429 18.0864 14.5666C17.9615 14.6902 17.792 14.7596 17.6153 14.7596H6.49552C6.20996 14.7736 5.92505 14.7213 5.66359 14.6068C5.40213 14.4924 5.17138 14.319 4.98978 14.1005C4.7345 13.78 4.60016 13.3816 4.60988 12.9737C4.61961 12.5658 4.77281 12.174 5.04308 11.8659L5.1297 11.7802L6.22236 10.8969L3.16425 2.31651L0.772393 1.59143C0.602759 1.53985 0.46078 1.42372 0.377691 1.26858C0.294601 1.11344 0.277206 0.932002 0.329333 0.764174C0.38146 0.596345 0.498839 0.455877 0.655647 0.373671C0.733291 0.332967 0.818278 0.307791 0.905756 0.299582C0.993233 0.291373 1.08149 0.300292 1.16548 0.325828L3.89713 1.15638C3.99992 1.18741 4.09359 1.24259 4.17016 1.31719C4.24673 1.3918 4.30392 1.48363 4.33685 1.58484L7.38164 10.1455H17.3755L19.1544 3.21298H9.06608C8.7813 3.21298 8.52872 3.03009 8.43984 2.75953C8.2998 2.33323 8.61737 1.89464 9.06608 1.89464H20.0272ZM18.7805 3.5096H9.06305C8.65012 3.5096 8.28387 3.24441 8.155 2.8521C7.95195 2.23397 8.41242 1.59802 9.06305 1.59802H20.0291C20.1718 1.59896 20.3125 1.63116 20.4412 1.6923C20.5686 1.75289 20.681 1.8404 20.7705 1.94866C20.8648 2.05852 20.9327 2.18807 20.9692 2.32759C21.006 2.46838 21.0099 2.61562 20.9805 2.75811L20.9792 2.76435L18.8672 11.0148M18.7805 3.5096L17.135 9.84884H7.60136L4.62275 1.4955L4.62224 1.49393C4.57449 1.34718 4.49156 1.21403 4.38054 1.10585C4.26959 0.997748 4.13387 0.917781 3.98495 0.872763L1.25356 0.0422902C1.13193 0.00531213 1.00412 -0.00760412 0.877446 0.00428294C0.750772 0.01617 0.627705 0.0526258 0.515271 0.111568C0.288201 0.230608 0.118228 0.434015 0.0427445 0.677043C-0.0327391 0.920072 -0.00755033 1.18281 0.11277 1.40747C0.23309 1.63212 0.438685 1.80029 0.684326 1.87497L2.92591 2.5545L5.87513 10.7946L4.92834 11.5599L4.82359 11.6636L4.81664 11.6715C4.50064 12.0318 4.32153 12.4898 4.31015 12.9667C4.29878 13.4436 4.45585 13.9095 4.75432 14.2841L4.75821 14.2889C4.96952 14.5431 5.23803 14.7449 5.54228 14.8781C5.84417 15.0102 6.17293 15.0712 6.50264 15.0563H17.6153C17.8715 15.0563 18.1173 14.9556 18.2984 14.7763C18.4796 14.5971 18.5814 14.354 18.5814 14.1005C18.5814 13.847 18.4796 13.6039 18.2984 13.4246C18.1172 13.2454 17.8715 13.1447 17.6153 13.1447H6.39542C6.37154 13.1433 6.34835 13.1361 6.32789 13.1238C6.30637 13.1108 6.28859 13.0925 6.27627 13.0708C6.26395 13.049 6.25752 13.0245 6.25758 12.9995C6.25761 12.9881 6.25901 12.9767 6.26172 12.9657L7.72969 11.7604H17.9051C18.1271 11.7649 18.3438 11.6936 18.5189 11.5585C18.695 11.4226 18.8181 11.2303 18.8672 11.0148M9.18053 18.2203C9.18053 19.2032 8.37514 20 7.38164 20C6.38814 20 5.58275 19.2032 5.58275 18.2203C5.58275 17.2373 6.38814 16.4405 7.38164 16.4405C8.37514 16.4405 9.18053 17.2373 9.18053 18.2203ZM18.2882 18.2203C18.2882 19.2032 17.4828 20 16.4893 20C15.4958 20 14.6905 19.2032 14.6905 18.2203C14.6905 17.2373 15.4958 16.4405 16.4893 16.4405C17.4828 16.4405 18.2882 17.2373 18.2882 18.2203ZM7.38164 19.7034C8.20955 19.7034 8.88071 19.0394 8.88071 18.2203C8.88071 17.4011 8.20955 16.7371 7.38164 16.7371C6.55372 16.7371 5.88257 17.4011 5.88257 18.2203C5.88257 19.0394 6.55372 19.7034 7.38164 19.7034ZM16.4893 19.7034C17.3173 19.7034 17.9884 19.0394 17.9884 18.2203C17.9884 17.4011 17.3173 16.7371 16.4893 16.7371C15.6614 16.7371 14.9903 17.4011 14.9903 18.2203C14.9903 19.0394 15.6614 19.7034 16.4893 19.7034Z" fill="white"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        // Обработчики событий для кнопок
        container.querySelectorAll('.btn-buy').forEach(btn => {
            btn.addEventListener('click', (e) => e.stopPropagation());
        });

        container.querySelectorAll('.btn-cart').forEach(btn => {
            btn.addEventListener('click', (e) => e.stopPropagation());
        });
    }

    initFilters();
});
