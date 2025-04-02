
// Функция для обновления счетчика корзины
function updateCartCounter(count) {
    const counter = document.getElementById('cart-counter');
    if (counter) {
        counter.textContent = count;
        counter.style.display = count > 0 ? 'block' : 'none';
        // Также можно обновить другие элементы, отображающие количество товаров
        const cartIcons = document.querySelectorAll('.cart-icon .counter');
        cartIcons.forEach(icon => {
            icon.textContent = count;
            icon.style.display = count > 0 ? 'block' : 'none';
        });
    }
}

// Функция для обновления счетчика сравнения
function updateComparisonCounter(count) {
    const counter = document.getElementById('comparison-counter');
    if (counter) {
        counter.textContent = count;
        counter.style.display = count > 0 ? 'block' : 'none';
    }
}

// Функция для показа уведомлений
function showNotification(message, type = 'success') {
    console.log(`${type}: ${message}`);
    // Пример простой реализации:
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}