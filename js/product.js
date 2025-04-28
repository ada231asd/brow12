document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    let userData = {
        favorites: [],
        comparisons: [],
        cart: []
    };

    // Функция для отправки события обновления счетчиков
    const dispatchCounterUpdateEvent = () => {
        const cartTotalQuantity = userData.cart.reduce((sum, item) => sum + item.quantity, 0);
        const comparisonItems = userData.comparisons.length;

        const event = new CustomEvent('updateCounters', {
            detail: {
                cartCount: cartTotalQuantity,
                comparisonCount: comparisonItems
            }
        });
        window.dispatchEvent(event);
    };

    // Загрузка данных пользователя
    const loadUserData = async () => {
        try {
            const response = await fetch('/brow12/api/get_full_user.php', {
                credentials: 'include'
            });

            const data = await response.json();
            if (data.status === 'success') {
                userData = {
                    favorites: data.data.favorites || [],
                    comparisons: data.data.comparisons || [],
                    cart: data.data.cart || []
                };
                updateButtonStates();
                dispatchCounterUpdateEvent();
            }
        } catch (error) {
            // Ошибка обрабатывается без логов
        }
    };

    // Обновление состояния кнопок избранного и сравнения
    const updateButtonStates = () => {
        const favoriteBtn = document.querySelector('.favorite-btn');
        const comparisonBtn = document.querySelector('.comparison-btn');
        const favoriteIcon = favoriteBtn?.querySelector('svg path');
        const comparisonIcon = comparisonBtn?.querySelector('svg path');

        const isFavorite = userData.favorites.some(f => f.product_id == productId);
        const isCompared = userData.comparisons.some(c => c.product_id == productId);

        if (favoriteBtn) {
            favoriteBtn.classList.toggle('active', isFavorite);
            if (favoriteIcon) favoriteIcon.setAttribute('fill', isFavorite ? '#fff' : '#8A33FD');
        }
        if (comparisonBtn) {
            comparisonBtn.classList.toggle('active', isCompared);
            if (comparisonIcon) comparisonIcon.setAttribute('fill', isCompared ? '#fff' : '#8A33FD');
        }
    };

    const initTabs = () => {
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;
                document.querySelectorAll('.tab-button, .tab-pane').forEach(el => {
                    el.classList.remove('active');
                });
                button.classList.add('active');
                const tabPane = document.getElementById(tabId);
                if (tabPane) tabPane.classList.add('active');
            });
        });
    };

    const showNotification = (message, type = 'success') => {
        const notificationContainer = document.getElementById('notificationContainer');
        if (!notificationContainer) return;

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notificationContainer.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    };

    const renderError = (message) => {
        const container = document.createElement('div');
        container.className = 'error-container';
        container.innerHTML = `
            <div class="alert">
                ${message}
                <button onclick="window.location.href='/brow12/index.php'">На главную</button>
            </div>
        `;
        document.body.prepend(container);
    };

    const loadProductData = async (productId) => {
        try {
            const response = await fetch(`/brow12/api/product.php?id=${productId}`);
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            
            const data = await response.json();
            if (data.status !== 'success') throw new Error(data.message || 'Error');

            renderProductPage(data.data);
            await loadUserData();
            initTabs();
            setupReviewForm();
            setupActionButtons();
        } catch (error) {
            renderError(`Ошибка загрузки: ${error.message}`);
        }
    };

    const renderProductPage = (apiData) => {
        const { product, characteristics, reviews } = apiData;
    
        const approvedReviews = reviews || [];
    
        const favoriteBtn = document.querySelector('.favorite-btn');
        const comparisonBtn = document.querySelector('.comparison-btn');
        const productImage = document.querySelector('.product-image');
        const productName = document.querySelector('.product-name');
        const productDescriptionTab = document.querySelector('.product-description-tab');
        const priceContainer = document.querySelector('.price-container');
        const productRating = document.querySelector('.product-rating');
        const reviewCount = document.querySelector('.review-count');
        const specsList = document.querySelector('.specifications-list');
        const reviewsList = document.querySelector('.reviews-list');
    
        if (favoriteBtn) favoriteBtn.dataset.productId = product.product_id || '';
        if (comparisonBtn) comparisonBtn.dataset.productId = product.product_id || '';
        if (productImage) productImage.src = product.image_url || '/img/default.png';
        if (productName) productName.textContent = product.product_name || 'Название отсутствует';
        if (productDescriptionTab) productDescriptionTab.textContent = product.description || 'Нет данных';
    
        // Цена и скидка
        if (priceContainer) {
            const finalPrice = product.final_price ?? product.price;
            const hasDiscount = product.discount > 0;
            const discountValue = hasDiscount ? (product.price - finalPrice) : 0;
    
            priceContainer.innerHTML = hasDiscount 
                ? `
                    <div class="price-block">
                        <div class="blok_old">
                            <div class="old-price">${Math.floor(product.price).toLocaleString('ru-RU')} ₽</div>
                            <div class="disk">
                                <div class="discount">-${Math.round(product.discount)}%</div>
                                <div class="discount-amount">-${discountValue.toLocaleString('ru-RU')} ₽</div>
                            </div>
                        </div>
                        <div class="final-price">${finalPrice.toLocaleString('ru-RU')} ₽</div>
                    </div>
                  `
                : `
                    <div class="price-block">
                        <div class="final-price">${finalPrice.toLocaleString('ru-RU')} ₽</div>
                    </div>
                  `;
        }
    
        // Рейтинг и количество отзывов
        if (productRating && reviewCount) {
            const reviewCountValue = approvedReviews.length;
            reviewCount.textContent = `(${reviewCountValue})`;
    
            if (reviewCountValue > 0) {
                const averageRating = approvedReviews.reduce((sum, r) => sum + Number(r.rating || 0), 0) / reviewCountValue;
                const roundedRating = Math.round(averageRating * 10) / 10; // Округляем до 1 знака
                const fullStars = Math.floor(averageRating);
                const hasHalfStar = averageRating - fullStars >= 0.5;
                productRating.innerHTML = `
                    ${'★'.repeat(fullStars)}${hasHalfStar ? '½' : ''}${'☆'.repeat(5 - fullStars - (hasHalfStar ? 1 : 0))}
                `;
            } else {
                productRating.textContent = '☆☆☆☆☆';
            }
        }
    
        // Характеристики
        if (specsList) {
            specsList.innerHTML = characteristics && characteristics.length > 0 
                ? characteristics.map(c => `
                    <li class="spec-item">
                        <span class="characteristic-name">${c.characteristic_name && c.characteristic_name.trim() ? c.characteristic_name : '—'}:</span>
                        <span class="characteristic-value">${c.characteristic_value && c.characteristic_value.trim() ? c.characteristic_value : '—'}</span>
                    </li>
                  `).join('')
                : '<li>Нет данных</li>';
        }
    
        // Отзывы
        if (reviewsList) {
            reviewsList.innerHTML = approvedReviews.length > 0
                ? approvedReviews.map(r => `
                    <div class="review">
                        <div class="review-header">
                            <span class="review-author">${r.author || 'Аноним'}</span>
                            <span class="review-date">${r.created_at ? new Date(r.created_at).toLocaleDateString('ru-RU') : 'Дата отсутствует'}</span>
                        </div>
                        <div class="review-rating">${'★'.repeat(Math.min(5, Math.max(0, r.rating || 0)))}${'☆'.repeat(5 - Math.min(5, Math.max(0, r.rating || 0)))}</div>
                        <div class="review-comment">${r.comment || 'Без комментария'}</div>
                    </div>
                `).join('')
                : '<div class="no-reviews">Отзывов пока нет</div>';
        }
    };

    const setupReviewForm = () => {
        const reviewForm = document.getElementById('reviewForm');
        if (!reviewForm) return;

        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const rating = reviewForm.querySelector('input[name="rating"]:checked')?.value;
            const comment = reviewForm.querySelector('#comment')?.value.trim();

            if (!rating || !comment) {
                showNotification('Пожалуйста, выберите оценку и напишите комментарий', 'error');
                return;
            }

            try {
                const response = await fetch('/brow12/api/add_review.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        product_id: productId,
                        rating,
                        comment
                    })
                });

                const result = await response.json();
                if (result.status === 'success') {
                    showNotification('Отзыв отправлен на модерацию');
                    reviewForm.reset();
                } else {
                    showNotification(result.message || 'Ошибка отправки отзыва', 'error');
                }
            } catch (error) {
                showNotification('Произошла ошибка', 'error');
            }
        });
    };

    const setupActionButtons = () => {
        const favoriteBtn = document.querySelector('.favorite-btn');
        const comparisonBtn = document.querySelector('.comparison-btn');
        const cartBtn = document.querySelector('.btn-cart');
        const buyBtn = document.querySelector('.btn-buy');
        const backBtn = document.querySelector('.back-btn');

        if (favoriteBtn) {
            favoriteBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch('/brow12/api/add_to_favorite.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'include',
                        body: JSON.stringify({ product_id: productId })
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        await loadUserData();
                        showNotification(result.message || 'Добавлено в избранное');
                    } else {
                        showNotification(result.message || 'Ошибка', 'error');
                    }
                } catch (error) {
                    showNotification('Произошла ошибка', 'error');
                }
            });
        }

        if (comparisonBtn) {
            comparisonBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch('/brow12/api/add_to_comparison.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'include',
                        body: JSON.stringify({ product_id: productId })
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        await loadUserData();
                        showNotification(result.message || 'Добавлено в сравнение');
                    } else {
                        showNotification(result.message || 'Ошибка', 'error');
                    }
                } catch (error) {
                    showNotification('Произошла ошибка', 'error');
                }
            });
        }

        if (cartBtn) {
            cartBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch('/brow12/api/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'include',
                        body: JSON.stringify({ product_id: productId })
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        await loadUserData();
                        showNotification(result.message || 'Товар добавлен в корзину');
                        cartBtn.classList.add('animate');
                        setTimeout(() => cartBtn.classList.remove('animate'), 1000);
                    } else {
                        showNotification(result.message || 'Ошибка', 'error');
                    }
                } catch (error) {
                    showNotification('Произошла ошибка', 'error');
                }
            });
        }

        if (buyBtn) {
            buyBtn.addEventListener('click', () => {
                showNotification('Функция покупки в 1 клик в разработке', 'info');
            });
        }

        if (backBtn) {
            backBtn.addEventListener('click', () => {
                const referrer = document.referrer;
                if (referrer && referrer !== window.location.href && referrer.includes(window.location.hostname)) {
                    window.location.href = referrer;
                } else {
                    if (history.length > 1) {
                        history.back();
                    } else {
                        window.location.href = '/brow12/index.php';
                    }
                }
            });
        }
    };

    if (productId) {
        loadProductData(productId);
    } else {
        renderError('Товар не найден!');
    }
});