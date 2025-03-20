document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    const initTabs = () => {
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;
                document.querySelectorAll('.tab-button, .tab-pane').forEach(el => {
                    el.classList.remove('active');
                });
                button.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    };

    const renderError = (message) => {
        const container = document.createElement('div');
        container.className = 'error-container';
        container.innerHTML = `
            <div class="alert alert-danger">
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
            if (data.status !== 'success') throw new Error(data.message || 'Ошибка сервера');

            renderProductPage(data.data);
            initTabs();

        } catch (error) {
            renderError(`Ошибка загрузки: ${error.message}`);
        }
    };

    const renderProductPage = (apiData) => {
        const { product, characteristics, reviews } = apiData;

        // Основная информация
        document.querySelector('.product-image').src = product.image_url || '/img/default.png';
        document.querySelector('.product-name').textContent = product.product_name;
        document.querySelector('.product-category').textContent = product.category_name;
        document.querySelector('.product-description').textContent = product.description || 'Описание отсутствует';

        // Цена
        const priceContainer = document.querySelector('.price-container');
        const finalPrice = product.final_price ?? product.price;
        priceContainer.innerHTML = product.discount > 0 
            ? `
                <span class="old-price">${product.price.toLocaleString('ru-RU')} ₽</span>
                <span class="discount">-${Math.round(product.discount)}%</span>
                <span class="final-price">${finalPrice.toLocaleString('ru-RU')} ₽</span>
              `
            : `<span class="final-price">${product.price.toLocaleString('ru-RU')} ₽</span>`;

        // Характеристики
        const specsList = document.querySelector('.specifications-list');
        specsList.innerHTML = characteristics.length > 0 
            ? characteristics.map(c => `
                <li class="spec-item">
                    <span class="characteristic-name">${c.characteristic_name}:</span>
                    <span class="characteristic-value">${c.characteristic_value || '—'}</span>
                </li>
              `).join('')
            : '<li>Характеристики не указаны</li>';

        // Отзывы
        const reviewsList = document.querySelector('.reviews-list');
        reviewsList.innerHTML = reviews.length > 0
            ? reviews.map(r => `
                <div class="review">
                    <div class="review-header">
                        <span class="review-author">${r.author || 'Аноним'}</span>
                        <span class="review-date">${new Date(r.created_at).toLocaleDateString('ru-RU')}</span>
                    </div>
                    <div class="review-rating">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</div>
                    <div class="review-comment">${r.comment || 'Без комментария'}</div>
                </div>
              `).join('')
            : '<div class="no-reviews">Отзывов пока нет</div>';
    };

    productId ? loadProductData(productId) : renderError('Товар не найден!');
});