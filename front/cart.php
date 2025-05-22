<?php

// Проверка авторизации через куки
if (!isset($_COOKIE['auth_token'])) {
    header("Location: auth.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
        /* Стили для аккордеона */
        .accordion-block {
            background-color: #444444;
            border-radius: 12px;
            margin-bottom: 10px;
            padding: 20px;
        }
        .accordion-block.collapsed .accordion-content {
            display: none;
        }
        .accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .accordion-header h2 {
            font-size: 20px;
            margin: 0;
        }
        .btn-edit-step {
            background: none;
            border: none;
            color: #4E1E6D;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-next-step {
            background: #4E1E6D;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            margin-top: 20px;
            cursor: pointer;
        }
        .btn-next-step:hover {
            background: #1f0048;
        }
    </style>
</head>
<body>
<?php require '../Header/header.html'; ?>

<div class="cart" style="display:flex; gap:40px; align-items:flex-start;">
    <div style="flex:2;">
        <div class="accordion-block" id="order-block" style="display:block;">
            <div class="accordion-header">
                <span class="cart-title">Ваш заказ</span>
                <button class="btn-next-step" id="collapse-order-btn">Свернуть</button>
            </div>
            <div class="accordion-content" id="order-content">
                <div class="cart-items" id="cart-items"></div>
            </div>
            <div class="accordion-collapsed" id="order-collapsed" style="display:none; align-items:center; justify-content:space-between; min-height:90px;">
                <div id="collapsed-images" style="display:flex; gap:16px; align-items:center;"></div>
                <button class="btn-edit-step" id="expand-order-btn" style="background:#4E1E6D; color:white; border:none; padding:10px 30px; border-radius:8px; font-size:16px; font-weight:500; cursor:pointer;">Изменить</button>
            </div>
            <button class="btn-next-step" id="order-next-btn" style="margin-top:20px; width:160px;">Далее</button>
        </div>
        <div class="accordion-block" id="delivery-block" style="margin-top:24px; display:none;">
            <div class="accordion-header">
                <span class="cart-title">Способ получения</span>
                <button class="btn-next-step" id="collapse-delivery-btn">Свернуть</button>
            </div>
            <div class="accordion-content" id="delivery-content">
                <div style="display:flex; gap:24px; align-items:flex-start;">
                    <div style="flex:1; min-width:260px;">
                        <div id="delivery-city-block">
                            <label style="font-size:15px;">Ваш город</label>
                            <input type="text" id="delivery-city" value="Санкт-Петербург" style="width:100%; margin-bottom:16px; padding:8px; border-radius:6px; border:none;">
                        </div>
                        <div style="display:flex; gap:16px; margin-bottom:16px;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="delivery-type" value="delivery" checked style="accent-color:#A230FF;"> <span>Доставка</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="delivery-type" value="pickup" style="accent-color:#A230FF;"> <span>Самовывоз</span>
                            </label>
                        </div>
                        <div id="delivery-form">
                            <!-- Форма доставки по умолчанию -->
                            <div style="display:flex; gap:16px; margin-bottom:12px;">
                                <div style="flex:1;">
                                    <label style="font-size:13px;">Дата</label>
                                    <input type="text" id="delivery-date" value="Завтра, 11 июля, вс" style="width:100%; padding:8px; border-radius:6px; border:none;">
                                </div>
                                <div style="flex:1;">
                                    <label style="font-size:13px;">Улица, дом/корпус</label>
                                    <input type="text" id="delivery-address" style="width:100%; padding:8px; border-radius:6px; border:none;">
                                </div>
                            </div>
                            <div style="display:flex; gap:16px; margin-bottom:12px;">
                                <div style="flex:1;">
                                    <label style="font-size:13px;">Время</label>
                                    <select id="delivery-time" style="width:100%; padding:8px; border-radius:6px; border:none;">
                                        <option>10:00–13:00 (бесплатно)</option>
                                        <option>13:00–15:00 (бесплатно)</option>
                                        <option>15:00–18:00 (бесплатно)</option>
                                        <option>18:00–21:00 (бесплатно)</option>
                                    </select>
                                </div>
                                <div style="flex:1;">
                                    <label style="font-size:13px;">Квартира</label>
                                    <input type="text" id="delivery-flat" style="width:100%; padding:8px; border-radius:6px; border:none;">
                                </div>
                            </div>
                            <div style="margin-bottom:12px;">
                                <label style="font-size:13px;">Комментарий курьеру</label>
                                <input type="text" id="delivery-comment" style="width:100%; padding:8px; border-radius:6px; border:none;">
                            </div>
                        </div>
                        <div id="pickup-form" style="display:none;">
                            <div style="display:flex; gap:16px;">
                                <div style="flex:2; max-height:180px; overflow-y:auto;">
                                    <div id="pickup-stores"></div>
                                </div>
                                <div style="flex:1; min-width:180px;">
                                    <div id="pickup-map" style="height:160px; border-radius:8px; overflow:hidden;"></div>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; gap:16px; margin-top:20px;">
                            <button class="btn-next-step" id="delivery-back-btn" style="width:160px; background:#222; color:white;">Назад</button>
                            <button class="btn-next-step" id="delivery-next-btn" style="width:160px;">Далее</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-collapsed" id="delivery-collapsed" style="display:none; align-items:center; justify-content:space-between; min-height:90px;">
                <div id="collapsed-delivery-info" style="font-size:16px; color:white;"></div>
                <button class="btn-edit-step" id="expand-delivery-btn" style="background:#4E1E6D; color:white; border:none; padding:10px 30px; border-radius:8px; font-size:16px; font-weight:500; cursor:pointer;">Изменить</button>
            </div>
        </div>
        <div class="accordion-block" id="payment-block" style="margin-top:24px; display:none;">
            <div class="accordion-header">
                <span class="cart-title">Способ оплаты</span>
            </div>
            <div class="accordion-content" id="payment-content">
                <div style="display:flex; flex-direction:column; gap:18px; max-width:400px;">
                    <label for="payment-method">Выберите способ оплаты</label>
                    <select id="payment-method" style="padding:10px; border-radius:6px; border:none; font-size:16px;">
                        <option value="card_online">Картой онлайн</option>
                        <option value="cash">Наличными курьеру</option>
                        <option value="card_courier">Картой курьеру</option>
                    </select>
                    <div style="display:flex; gap:16px; margin-top:20px;">
                        <button class="btn-next-step" id="payment-back-btn" style="width:160px; background:#222; color:white;">Назад</button>
                        <button class="btn-next-step" id="payment-next-btn" style="width:160px;">Далее</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="accordion-block" id="recipient-block" style="margin-top:24px; display:none;">
            <div class="accordion-header">
                <span class="cart-title">Получатель</span>
            </div>
            <div class="accordion-content" id="recipient-content">
                <div style="display:flex; flex-wrap:wrap; gap:16px;">
                    <input type="text" id="recipient-name" placeholder="Например, Иван" style="flex:1; min-width:180px; padding:10px; border-radius:6px; border:none;">
                    <input type="text" id="recipient-surname" placeholder="Например, Иванов" style="flex:1; min-width:180px; padding:10px; border-radius:6px; border:none;">
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:16px; margin-top:12px;">
                    <input type="tel" id="recipient-phone" placeholder="+7 (9__) ___-__-__" style="flex:1; min-width:180px; padding:10px; border-radius:6px; border:none;">
                    <input type="email" id="recipient-email" placeholder="Например, smart@gmail.com" style="flex:1; min-width:180px; padding:10px; border-radius:6px; border:none;">
                </div>
                <div style="margin-top:12px;">
                    <label style="display:flex; align-items:center; gap:8px; font-size:15px;">
                        <input type="checkbox" id="recipient-no-call" style="accent-color:#A230FF;"> Не перезванивать мне для подтверждения заказа
                    </label>
                </div>
                <div style="display:flex; gap:16px; margin-top:20px;">
                    <button class="btn-next-step" id="recipient-back-btn" style="width:160px; background:#222; color:white;">Назад</button>
                </div>
            </div>
        </div>
    </div>
    <div style="flex:1; min-width:320px;">
        <div id="summary-block" style="background:#444; border-radius:12px; padding:24px; margin-bottom:16px;">
            <div style="font-size:22px; font-weight:600; margin-bottom:18px;">Итого</div>
            <div class="cart-summary-row"><span id="summary-count">0 товара на сумму</span> <span id="summary-sum">0 ₽</span></div>
            <div class="cart-summary-row"><span>Стоимость доставки</span> <span>бесплатно</span></div>
            <div class="cart-summary-total" style="font-size:26px; font-weight:700; margin-top:20px;">К оплате <span id="summary-total">0 ₽</span></div>
            <button id="checkout-btn" class="cart-checkout" style="margin-top:18px;">Оформить заказ</button>
        </div>
        <div style="margin-top:8px;">
            <label style="display:flex; align-items:flex-start; gap:8px; font-size:14px;">
                <input type="checkbox" id="agree-checkbox" style="accent-color:#A230FF; margin-top:2px;" checked>
                <span>Подтверждая заказ, я принимаю условия <a href="#" style="color:#A230FF; text-decoration:underline;">пользовательского соглашения</a></span>
            </label>
        </div>
    </div>
</div>

<!-- Всплывающее окно -->
<div id="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:#222; color:white; padding:30px 40px; border-radius:12px; font-size:18px; min-width:300px; text-align:center;">
        <span id="modal-message">Товара больше нет в наличии</span><br><br>
        <button onclick="document.getElementById('modal-overlay').style.display='none'" style="background:#4E1E6D; color:white; border:none; padding:10px 30px; border-radius:8px; cursor:pointer;">OK</button>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// --- Глобальные переменные для блоков (объявляем только один раз!) ---
const orderBlock = document.getElementById('order-block');
const deliveryBlock = document.getElementById('delivery-block');
const paymentBlock = document.getElementById('payment-block');
const recipientBlock = document.getElementById('recipient-block');
// ... если появятся новые блоки, объявлять их здесь ...

// Получение корзины и рендер
let lastCartItems = [];

function fetchCart() {
    console.log('Fetching cart...');
    console.log('Auth token:', document.cookie);
    fetch('../api/cart_api.php', {
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(r => {
        console.log('Response status:', r.status);
        return r.json();
    })
    .then(data => {
        console.log('Cart data:', data);
        if (data.status === 'success') {
            lastCartItems = data.data;
            let orderBlock = document.getElementById('order-block');
            let deliveryBlock = document.getElementById('delivery-block');
            let paymentBlock = document.getElementById('payment-block');
            let recipientBlock = document.getElementById('recipient-block');
            let summaryBlock = document.getElementById('summary-block');
            let agreeBlock = document.getElementById('agree-checkbox')?.parentElement?.parentElement;
            if (data.data.length === 0) {
                if (orderBlock) orderBlock.style.display = 'none';
                if (deliveryBlock) deliveryBlock.style.display = 'none';
                if (paymentBlock) paymentBlock.style.display = 'none';
                if (recipientBlock) recipientBlock.style.display = 'none';
                if (summaryBlock) summaryBlock.style.display = 'none';
                if (agreeBlock) agreeBlock.style.display = 'none';
                let empty = document.getElementById('cart-empty');
                if (!empty) {
                    empty = document.createElement('div');
                    empty.id = 'cart-empty';
                    empty.className = 'cart-empty';
                    empty.innerText = 'Товаров в корзине нет';
                    document.querySelector('.cart').prepend(empty);
                }
                empty.style.display = 'block';
            } else {
                let empty = document.getElementById('cart-empty');
                if (empty) empty.style.display = 'none';
                if (orderBlock) orderBlock.style.display = 'block';
                if (agreeBlock) agreeBlock.style.display = '';
                renderCartItems(data.data);
                updateSummary(data.data);
                if (summaryBlock) summaryBlock.style.display = '';
            }
        }
    });
}

function renderCartItems(items) {
    const container = document.getElementById('cart-items');
        container.innerHTML = '';
    items.forEach(item => {
        // Исправляем путь к фото
        let imgPath = item.image_url;
        if (!imgPath.startsWith('full_image/produkts/')) {
            imgPath = 'full_image/produkts/defoult.jpg';
        }
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div class="cart-item-image"><img src="../${imgPath}" alt=""></div>
            <div class="cart-item-info">
                <div>
                    <div class="cart-item-title">${item.name}</div>
                    </div>
                        <div class="cart-item-prices">
                    ${item.discount > 0 ? `<span class='cart-item-price-old'>${parseInt(item.price)} ₽</span>` : ''}
                    <span class="cart-item-price">${parseInt(item.final_price)} ₽</span>
                        </div>
                        <div class="cart-item-quantity-control">
                    <button class="quantity-btn" onclick="changeQuantity(${item.cart_item_id}, ${item.quantity-1}, ${item.stock_quantity})" ${item.quantity<=1?'disabled':''}>-</button>
                    <span class="quantity-value">${parseInt(item.quantity)}</span>
                    <button class="quantity-btn" onclick="changeQuantity(${item.cart_item_id}, ${item.quantity+1}, ${item.stock_quantity})">+</button>
                </div>
            </div>
            <button class="cart-item-remove" onclick="removeCartItem(${item.cart_item_id})">🗑️</button>
        `;
        container.appendChild(div);
    });
    // Для свернутого вида — только картинки
    const collapsed = document.getElementById('collapsed-images');
    collapsed.innerHTML = '';
    items.forEach(item => {
        let imgPath = item.image_url;
        if (!imgPath.startsWith('full_image/produkts/')) {
            imgPath = 'full_image/produkts/defoult.jpg';
        }
        const img = document.createElement('img');
        img.src = '../' + imgPath;
        img.style.width = '60px';
        img.style.height = '60px';
        img.style.objectFit = 'contain';
        collapsed.appendChild(img);
    });
}

function updateSummary(items) {
    let count = 0;
    let sum = 0;
    items.forEach(item => {
        count += parseInt(item.quantity);
        sum += parseInt(item.final_price) * parseInt(item.quantity);
    });
    document.getElementById('summary-count').innerText = `${count} ${count === 1 ? 'товар' : (count < 5 ? 'товара' : 'товаров')} на сумму`;
    document.getElementById('summary-sum').innerText = `${sum} ₽`;
    document.getElementById('summary-total').innerText = `${sum} ₽`;
}

function changeQuantity(cart_item_id, new_quantity, stock_quantity) {
    if (new_quantity > stock_quantity) {
        document.getElementById('modal-message').innerText = 'Товара больше нет в наличии';
        document.getElementById('modal-overlay').style.display = 'flex';
        return;
    }
    if (new_quantity < 1) return;
    fetch('../api/cart_api.php', {
        method: 'POST',
        credentials: 'include',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({cart_item_id, quantity: new_quantity})
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') fetchCart();
    });
}

function removeCartItem(cart_item_id) {
    fetch('../api/cart_api.php', {
        method: 'DELETE',
        credentials: 'include',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({cart_item_id})
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') fetchCart();
    });
}

// Аккордеон логика
const orderContent = document.getElementById('order-content');
const orderCollapsed = document.getElementById('order-collapsed');
const collapseBtn = document.getElementById('collapse-order-btn');
const expandBtn = document.getElementById('expand-order-btn');

collapseBtn.onclick = function() {
    orderContent.style.display = 'none';
    orderCollapsed.style.display = 'flex';
    collapseBtn.style.display = 'none';
};

expandBtn.onclick = function() {
    orderContent.style.display = 'block';
    orderCollapsed.style.display = 'none';
    collapseBtn.style.display = 'inline-block';
};

// Оформление заказа
const checkoutBtn = document.getElementById('checkout-btn');
checkoutBtn.onclick = function() {
    if (!document.getElementById('agree-checkbox').checked) {
        document.getElementById('modal-message').innerText = 'Необходимо принять условия пользовательского соглашения';
        document.getElementById('modal-overlay').style.display = 'flex';
        return;
    }
    // Собираем все данные
    const deliveryType = document.querySelector('input[name="delivery-type"]:checked').value;
    const deliveryCity = document.getElementById('delivery-city').value;
    const deliveryAddress = document.getElementById('delivery-address').value;
    const deliveryDate = document.getElementById('delivery-date').value;
    const deliveryTime = document.getElementById('delivery-time').value;
    const deliveryFlat = document.getElementById('delivery-flat').value;
    const deliveryComment = document.getElementById('delivery-comment').value;
    let pickupStore = null;
    if (deliveryType === 'pickup') {
        const storeRadio = document.querySelector('input[name="pickup-store"]:checked');
        if (storeRadio) pickupStore = storeRadio.dataset.address;
    }
    const paymentMethod = document.getElementById('payment-method').value;
    const recipientName = document.getElementById('recipient-name').value;
    const recipientSurname = document.getElementById('recipient-surname').value;
    const recipientPhone = document.getElementById('recipient-phone').value;
    const recipientEmail = document.getElementById('recipient-email').value;
    const recipientNoCall = document.getElementById('recipient-no-call').checked;

    // Валидация (минимальная)
    if (!recipientName || !recipientPhone || !recipientEmail) {
        document.getElementById('modal-message').innerText = 'Пожалуйста, заполните все данные о получателе';
        document.getElementById('modal-overlay').style.display = 'flex';
        return;
    }
    // Формируем адрес для заказа
    let address = '';
            if (deliveryType === 'delivery') {
        address = deliveryCity + ', ' + deliveryAddress + (deliveryFlat ? ', кв. ' + deliveryFlat : '') + (deliveryComment ? ', ' + deliveryComment : '');
            } else {
        address = pickupStore;
    }
    // Отправляем заказ
    fetch('../api/order_api.php', {
                method: 'POST',
        credentials: 'include',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            address,
            payment_method: paymentMethod,
            recipient_name: recipientName,
            recipient_surname: recipientSurname,
            recipient_phone: recipientPhone,
            recipient_email: recipientEmail,
            recipient_no_call: recipientNoCall,
            delivery_type: deliveryType,
            delivery_date: deliveryDate,
            delivery_time: deliveryTime
        })
    }).then(r => r.json()).then(data => {
        console.log(data);
        if (data.status === 'success' || data.success === true) {
            window.location.href = 'thankyou.php?order_code=' + encodeURIComponent(data.order_code);
                } else {
            document.getElementById('modal-message').innerText = data.message || 'Ошибка оформления заказа';
            document.getElementById('modal-overlay').style.display = 'flex';
        }
    });
};

// --- DELIVERY BLOCK ---
const deliveryContent = document.getElementById('delivery-content');
const deliveryCollapsed = document.getElementById('delivery-collapsed');
const collapseDeliveryBtn = document.getElementById('collapse-delivery-btn');
const expandDeliveryBtn = document.getElementById('expand-delivery-btn');

collapseDeliveryBtn.onclick = function() {
    deliveryContent.style.display = 'none';
    deliveryCollapsed.style.display = 'flex';
    collapseDeliveryBtn.style.display = 'none';
    // Краткая инфа
    const type = document.querySelector('input[name="delivery-type"]:checked').value;
    if (type === 'delivery') {
        const city = document.getElementById('delivery-city').value;
        const addr = document.getElementById('delivery-address').value;
        document.getElementById('collapsed-delivery-info').innerText = `Доставка: ${city}${addr ? ', ' + addr : ''}`;
            } else {
        const store = document.querySelector('input[name="pickup-store"]:checked');
        document.getElementById('collapsed-delivery-info').innerText = store ? `Самовывоз: ${store.dataset.address}` : 'Самовывоз';
    }
};
expandDeliveryBtn.onclick = function() {
    deliveryContent.style.display = 'block';
    deliveryCollapsed.style.display = 'none';
    collapseDeliveryBtn.style.display = 'inline-block';
};

// Переключение форм
const deliveryTypeInputs = document.querySelectorAll('input[name="delivery-type"]');
deliveryTypeInputs.forEach(inp => {
    inp.onchange = function() {
        if (this.value === 'delivery') {
            document.getElementById('delivery-form').style.display = '';
            document.getElementById('pickup-form').style.display = 'none';
        } else {
            document.getElementById('delivery-form').style.display = 'none';
            document.getElementById('pickup-form').style.display = '';
            renderPickupStores();
        }
    };
});

// Переходы между блоками
document.getElementById('order-next-btn').onclick = function() {
    orderBlock.style.display = 'none';
    deliveryBlock.style.display = 'block';
    setTimeout(() => {
        if (window.pickupMap) window.pickupMap.invalidateSize();
    }, 100);
};
document.getElementById('delivery-back-btn').onclick = function() {
    deliveryBlock.style.display = 'none';
    orderBlock.style.display = 'block';
};
document.getElementById('delivery-next-btn').onclick = function() {
    deliveryBlock.style.display = 'none';
    paymentBlock.style.display = 'block';
};
document.getElementById('payment-back-btn').onclick = function() {
    paymentBlock.style.display = 'none';
    deliveryBlock.style.display = 'block';
    setTimeout(() => {
        if (window.pickupMap) window.pickupMap.invalidateSize();
    }, 100);
};
document.getElementById('payment-next-btn').onclick = function() {
    paymentBlock.style.display = 'none';
    recipientBlock.style.display = 'block';
    loadRecipientData();
};
document.getElementById('recipient-back-btn').onclick = function() {
    recipientBlock.style.display = 'none';
    paymentBlock.style.display = 'block';
};

// --- Автозаполнение получателя ---
function loadRecipientData() {
    fetch('../api/get_full_user.php', {credentials: 'include'})
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.data.user) {
                document.getElementById('recipient-name').value = data.data.user.name || '';
                document.getElementById('recipient-surname').value = data.data.user.surname || '';
                document.getElementById('recipient-phone').value = data.data.user.phone || '';
                document.getElementById('recipient-email').value = data.data.user.email || '';
            }
        });
}

// --- PICKUP STORES + MAP ---
function renderPickupStores() {
    // Магазины из базы (пример, можно ajax)
    const stores = [
        {id:2, name:'Северный филиал', address:'г. Москва, Ленинградский пр-т, д. 45', lat:55.794521, lng:37.535772, hours:'Пн-Пт: 10:00-20:00, Сб-Вс: 10:00-19:00'},
        {id:3, name:'Южный ТЦ "Гагаринский"', address:'г. Москва, ул. Вавилова, д. 3, ТЦ "Гагаринский", 2 этаж', lat:55.709123, lng:37.587645, hours:'Ежедневно 10:00-22:00'},
        {id:4, name:'Западный склад-магазин', address:'г. Москва, ул. Молодогвардейская, д. 54', lat:55.734567, lng:37.456789, hours:'Пн-Пт: 9:00-19:00, Сб: 10:00-17:00, Вс: выходной'},
        {id:5, name:'Восточный бутик', address:'г. Москва, Щёлковское ш., д. 75, ТРЦ "Щёлково", 1 этаж', lat:55.812345, lng:37.789012, hours:'Ежедневно 10:00-21:00'}
    ];
    const storesDiv = document.getElementById('pickup-stores');
    storesDiv.innerHTML = stores.map((s,i) => `
        <label style="display:flex; align-items:flex-start; gap:8px; margin-bottom:8px; cursor:pointer; width:100%;">
            <input type="radio" name="pickup-store" value="${s.id}" data-address="${s.address}" data-lat="${s.lat}" data-lng="${s.lng}" ${i===0?'checked':''}>
            <span><b>${s.address}</b><br><span style='font-size:13px; color:#aaa;'>${s.hours}</span></span>
        </label>
    `).join('');
    // Карта
    setTimeout(() => {
        if (window.pickupMap) window.pickupMap.remove();
        const first = stores[0];
        window.pickupMap = L.map('pickup-map').setView([first.lat, first.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(window.pickupMap);
        stores.forEach(s => {
            L.marker([s.lat, s.lng]).addTo(window.pickupMap).bindPopup(`<b>${s.name}</b><br>${s.address}<br>${s.hours}`);
        });
        // Центрирование по клику
        document.querySelectorAll('input[name="pickup-store"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                window.pickupMap.setView([lat, lng], 15);
            });
        });
        setTimeout(() => {
            window.pickupMap.invalidateSize();
        }, 100);
    }, 100);
}
// Если самовывоз выбран по умолчанию — отрисовать карту
if (document.querySelector('input[name="delivery-type"]:checked').value === 'pickup') renderPickupStores();

// Инициализация
fetchCart();
    </script>

</body>
</html>