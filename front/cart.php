<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #141414;
            color: white;
        }
        /* Основные стили корзины */
        .cart {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .cart-title {
            font-size: 28px;
            font-weight: 600;
        }
        .cart-clear {
            background: none;
            border: none;
            color: #FF3B30;
            font-size: 16px;
            cursor: pointer;
            padding: 8px 12px;
        }
        .cart-empty {
            text-align: center;
            padding: 60px 0;
            font-size: 18px;
            color: #666;
            display: none;
        }
        /* Стили товаров */
        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 30px;
        }
        .cart-item {
            display: flex;
            padding: 16px;
            background-color: #444444;
            border-radius: 12px;
            margin-left: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: relative;
        }
        .cart-item-image {
            width: 120px;
            height: 120px;
            flex-shrink: 0;
            margin-right: 20px;
            margin-left: 25px;
        }
        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .cart-item-info {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 265px;
        }
        .cart-item-title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
            display: flex;
            max-width: 95px;
        }
        .cart-item-prices {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            margin-top: 25px;
        }
        .cart-item-price {
            font-size: 18px;
            font-weight: 600;
        }
        .cart-item-price-old {
            font-size: 16px;
            color: #999;
            text-decoration: line-through;
        }
        .cart-item-discount {
            font-size: 14px;
            color: white;
            background: rgba(162, 48, 255, 0.288);
            padding: 2px 6px;
            border-radius: 4px;
            z-index: 1;
            position: relative;
            left: 85px;
            top: -70px;
        }
        .cart-item-quantity {
            font-size: 16px;
            color: #666;
        }
        .cart-item-remove {
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 4px;
        }
        .cart-item-remove:hover {
            color: #FF3B30;
        }
        /* Итоговая информация */
        .cart-summary {
            display: flex;
            margin-left: 25px;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: none;
        }
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 16px;
        }
        .cart-summary-total {
            font-size: 20px;
            font-weight: 600;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #EEE;
        }
        .cart-checkout {
            width: 100%;
            padding: 16px;
            background: #4E1E6D;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            margin-top: 20px;
            cursor: pointer;
        }
        .cart-checkout:hover {
            background: #1f0048;
        }
        .cart-item-quantity-control {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
        }
        .quantity-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid #ddd;
            color: white;
            background-color: #444444;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .quantity-value {
            font-size: 16px;
            min-width: 24px;
            text-align: center;
        }
        /* Стили для счетчиков */
        .cart-icon, .comparison-link {
            position: relative;
            display: inline-block;
        }
        #cart-counter, #comparison-counter {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #4E1E6D;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
    </style>
</head>
<body>
<?php require '../Header/header.html'; ?>
    <div class="cart">
        <div class="cart-header">
            <h1 class="cart-title">Корзина</h1>
            <button class="cart-clear">Очистить корзину</button>
        </div>
        
        <div class="cart-empty">
            Ваша корзина пуста
        </div>
        
        <div class="cart-content">
            <div class="cart-items"></div>
            
            <div class="cart-summary">
                <div class="cart-summary-row">
                    <span>Товары:</span>
                    <span class="cart-total-price">0 ₽</span>
                </div>
                <div class="cart-summary-row">
                    <span>Скидка:</span>
                    <span class="cart-total-discount">0 ₽</span>
                </div>
                <div class="cart-summary-row cart-summary-total">
                    <span>Итого:</span>
                    <span class="cart-final-price">0 ₽</span>
                </div>
                <button class="cart-checkout">Перейти к оформлению</button>
            </div>
        </div>
    </div>

    <script>
class Cart {
    static async handleResponse(response) {
        if (response.status === 401) {
            window.location.href = '/login.php';
            throw new Error('Требуется авторизация');
        }
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message);
        }
        return response.json();
    }

    static async getItems() {
        return fetch('../api/cart_api.php', { credentials: 'include' })
            .then(this.handleResponse);
    }

    static async updateItem(cartItemId, quantity) {
        return fetch('../api/cart_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({cart_item_id: cartItemId, quantity}),
            credentials: 'include'
        }).then(this.handleResponse);
    }

    static async removeItem(cartItemId) {
        return fetch('../api/cart_api.php', {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({cart_item_id: cartItemId}),
            credentials: 'include'
        }).then(this.handleResponse);
    }

    static async clear() {
        return fetch('../api/cart_api.php', {
            method: 'DELETE',
            credentials: 'include'
        }).then(this.handleResponse);
    }
}

class UserData {
    static cacheExpiry = 5 * 60 * 1000; // 5 минут
    
    static async getFullUserData(forceRefresh = false) {
        // Проверяем кэш
        const cachedData = localStorage.getItem('userDataCache');
        const cachedTime = localStorage.getItem('userDataCacheTime');
        
        if (!forceRefresh && cachedData && cachedTime && 
            Date.now() - parseInt(cachedTime) < this.cacheExpiry) {
            return JSON.parse(cachedData);
        }
        
        try {
            const response = await fetch('../api/get_full_user.php', {
                credentials: 'include'
            });
            
            if (response.status === 401) {
                window.location.href = '/login.php';
                throw new Error('Требуется авторизация');
            }
            
            if (!response.ok) {
                throw new Error('Ошибка при получении данных пользователя');
            }
            
            const data = await response.json();
            
            // Сохраняем в кэш
            localStorage.setItem('userDataCache', JSON.stringify(data));
            localStorage.setItem('userDataCacheTime', Date.now().toString());
            
            return data;
        } catch (error) {
            console.error('Ошибка:', error);
            return null;
        }
    }
    
    static async updateCounters() {
        try {
            const data = await this.getFullUserData(true); // Принудительное обновление
            if (!data || !data.data) return;
            
            // Обновляем счетчик корзины
            const cartCounter = document.getElementById('cart-counter');
            if (cartCounter) {
                const cartCount = data.data.cart.length;
                cartCounter.textContent = cartCount;
                cartCounter.style.display = cartCount > 0 ? 'inline-block' : 'none';
            }
            
            // Обновляем счетчик сравнения
            const comparisonCounter = document.getElementById('comparison-counter');
            if (comparisonCounter) {
                const comparisonCount = data.data.comparisons.length;
                comparisonCounter.textContent = comparisonCount;
                comparisonCounter.style.display = comparisonCount > 0 ? 'inline-block' : 'none';
            }
        } catch (error) {
            console.error('Ошибка при обновлении счетчиков:', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    let isUpdating = false;

    // Функция для обновления всей корзины
    const updateCart = async () => {
        if (isUpdating) return;
        isUpdating = true;
        
        try {
            const { data } = await Cart.getItems();
            renderCart(data);
            // Обновляем счетчики после изменения корзины
            await UserData.updateCounters();
        } catch (error) {
            console.error('Ошибка:', error);
            alert(error.message);
        } finally {
            isUpdating = false;
        }
    };

    // Привязка событий
    const bindEvents = () => {
        document.querySelector('.cart-clear').addEventListener('click', async () => {
            if (confirm('Очистить всю корзину?')) {
                await Cart.clear();
                await updateCart();
            }
        });

        document.querySelector('.cart-items').addEventListener('click', async (e) => {
            const item = e.target.closest('.cart-item');
            if (!item) return;

            const cartItemId = item.dataset.id;
            
            if (e.target.closest('.cart-item-remove')) {
                if (confirm('Удалить товар из корзины?')) {
                    await Cart.removeItem(cartItemId);
                    await updateCart();
                }
            }
            
            if (e.target.closest('.quantity-btn')) {
                const isPlus = e.target.classList.contains('plus');
                const quantityEl = item.querySelector('.quantity-value');
                let quantity = parseInt(quantityEl.textContent);
                
                quantity = isPlus ? quantity + 1 : quantity - 1;
                if (quantity < 1) quantity = 1;
                
                await Cart.updateItem(cartItemId, quantity);
                await updateCart();
            }
        });
    };

    // Рендер корзины
    const renderCart = (items) => {
        const container = document.querySelector('.cart-items');
        const emptyMsg = document.querySelector('.cart-empty');
        const summary = document.querySelector('.cart-summary');
        
        container.innerHTML = '';
        
        if (items.length === 0) {
            emptyMsg.style.display = 'block';
            summary.style.display = 'none';
            return;
        }
        
        emptyMsg.style.display = 'none';
        summary.style.display = 'block';
        
        let total = 0;
        let totalDiscount = 0;
        
        items.forEach(item => {
            const itemTotal = item.final_price * item.quantity;
            const itemDiscount = Math.floor(item.price * (item.discount / 100)) * item.quantity;
            
            total += itemTotal;
            totalDiscount += itemDiscount;
            
            const itemHTML = `
                <div class="cart-item" data-id="${item.cart_item_id}">
                    <div class="cart-item-image">
                        <img src="../${item.image_url || '/images/no-image.jpg'}" alt="${item.name}">
                    </div>
                    <div class="cart-item-info">
                        <h3 class="cart-item-title">${item.name}</h3>
                        <div class="cart-item-prices">
                            ${item.discount > 0 ? `
                                <span class="cart-item-price-old">${Math.floor(item.price)} ₽</span>
                                <span class="cart-item-price">${Math.floor(item.final_price)} ₽</span>
                                <span class="cart-item-discount">−${Math.floor(item.discount)}%</span>
                            ` : `
                                <span class="cart-item-price">${Math.floor(item.price)} ₽</span>
                            `}
                        </div>
                        <div class="cart-item-quantity-control">
                            <button class="quantity-btn minus">−</button>
                            <span class="quantity-value">${item.quantity}</span>
                            <button class="quantity-btn plus" 
                                ${item.quantity >= item.stock_quantity ? 'disabled' : ''}>+</button>
                        </div>
                    </div>
                    <button class="cart-item-remove">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', itemHTML);
        });
        
        document.querySelector('.cart-total-price').textContent = `${total} ₽`;
        document.querySelector('.cart-total-discount').textContent = `−${totalDiscount} ₽`;
        document.querySelector('.cart-final-price').textContent = `${total} ₽`;
    };

    // Инициализация
    updateCart().then(bindEvents);
    // Обновляем счетчики при загрузке страницы
    UserData.updateCounters();
});

// Глобальная функция для обновления счетчиков из других страниц
window.updateUserCounters = function() {
    UserData.updateCounters();
};
</script>
</body>
</html>