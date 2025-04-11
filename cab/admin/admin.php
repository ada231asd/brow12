<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            padding: 20px;
        }
        .sidebar nav ul {
            list-style: none;
        }
        .sidebar nav ul li {
            margin: 10px 0;
        }
        .sidebar nav ul li a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
        }
        .sidebar nav ul li a:hover {
            background: #34495e;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        header {
            background: #34495e;
            color: white;
            padding: 10px 20px;
            height: 60px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background: #34495e;
            color: white;
        }
        button {
            padding: 8px 15px;
            background: #2c3e50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #34495e;
        }
        .hidden {
            display: none;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            width: 500px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-content h3 {
            margin-bottom: 15px;
        }
        .modal-content label {
            display: block;
            margin-bottom: 5px;
        }
        .modal-content input, .modal-content select, .modal-content textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal-content .buttons {
            display: flex;
            justify-content: space-between;
        }
        .user-info a {
            color: white;
            text-decoration: none;
            margin-left: 10px;
        }
        .action-buttons {
            margin-bottom: 20px;
        }
        .error {
            color: red;
            font-size: 0.9em;
            margin-top: -10px;
            margin-bottom: 10px;
            display: none;
        }
        .product-image {
            max-width: 50px;
            max-height: 50px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>Админ-панель</h1>
            <div class="user-info">
                <span id="admin-name">Администратор</span>
                <a href="logout.php">Выйти</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="sidebar">
            <nav>
                <ul>
                    <li><a href="#" data-section="analytics">Аналитика</a></li>
                    <li><a href="#" data-section="users">Пользователи</a></li>
                    <li><a href="#" data-section="orders">Заказы</a></li>
                    <li><a href="#" data-section="products">Продукты</a></li>
                    <li><a href="#" data-section="categories">Категории</a></li>
                    <li><a href="#" data-section="characteristics">Характеристики</a></li>
                    <li><a href="#" data-section="news">Новости</a></li>
                    <li><a href="#" data-section="promotions">Акции</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="content">
            <div id="analytics" class="section hidden">
                <h2>Аналитика</h2>
                <button onclick="loadData('analytics')">Обновить данные</button>
                <table id="analytics-table">
                    <thead>
                        <tr>
                            <th>Метрика</th>
                            <th>Значение</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Общее количество пользователей</td><td id="total-users">0</td></tr>
                        <tr><td>Общее количество заказов</td><td id="total-orders">0</td></tr>
                        <tr><td>Общее количество продуктов</td><td id="total-products">0</td></tr>
                        <tr><td>Общая сумма заказов</td><td id="total-sales">0</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="users" class="section hidden">
                <h2>Пользователи</h2>
                <div class="search-bar">
                    <input type="text" id="user-search" placeholder="Поиск по имени или email" oninput="loadData('users', this.value)">
                </div>
                <table id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Телефон</th>
                            <th>Статус</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="orders" class="section hidden">
                <h2>Заказы</h2>
                <div class="search-bar">
                    <input type="text" id="order-search" placeholder="Поиск по имени пользователя или адресу" oninput="loadData('orders', this.value)">
                </div>
                <table id="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Код заказа</th>
                            <th>Пользователь</th>
                            <th>Статус заказа</th>
                            <th>Сумма</th>
                            <th>Адрес доставки</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="products" class="section hidden">
                <h2>Продукты</h2>
                <div class="action-buttons">
                    <button onclick="openAddProductModal()">Добавить продукт</button>
                </div>
                <div class="search-bar">
                    <input type="text" id="product-search" placeholder="Поиск по названию или описанию" oninput="loadData('products', this.value)">
                </div>
                <table id="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Изображение</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Цена</th>
                            <th>Скидка (%)</th>
                            <th>Количество</th>
                            <th>Бестселлер</th>
                            <th>Новый</th>
                            <th>Дата добавления</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="categories" class="section hidden">
                <h2>Категории</h2>
                <div class="action-buttons">
                    <button onclick="openAddCategoryModal()">Добавить категорию</button>
                </div>
                <div class="search-bar">
                    <input type="text" id="category-search" placeholder="Поиск по названию" oninput="loadData('categories', this.value)">
                </div>
                <table id="categories-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="characteristics" class="section hidden">
                <h2>Характеристики</h2>
                <div class="action-buttons">
                    <button onclick="openAddCharacteristicModal()">Добавить характеристику</button>
                </div>
                <div class="search-bar">
                    <input type="text" id="characteristic-search" placeholder="Поиск по названию" oninput="loadData('characteristics', this.value)">
                </div>
                <table id="characteristics-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Тип значения</th>
                            <th>Категория</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="news" class="section hidden">
                <h2>Новости</h2>
                <div class="action-buttons">
                    <button onclick="openAddNewsModal()">Добавить новость</button>
                </div>
                <div class="search-bar">
                    <input type="text" id="news-search" placeholder="Поиск по заголовку" oninput="loadData('news', this.value)">
                </div>
                <table id="news-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Заголовок</th>
                            <th>Изображение</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="promotions" class="section hidden">
                <h2>Акции</h2>
                <div class="action-buttons">
                    <button onclick="openAddPromotionModal()">Добавить акцию</button>
                </div>
                <div class="search-bar">
                    <input type="text" id="promotion-search" placeholder="Поиск по заголовку" oninput="loadData('promotions', this.value)">
                </div>
                <table id="promotions-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Заголовок</th>
                            <th>Изображение</th>
                            <th>Категория</th>
                            <th>Дата начала</th>
                            <th>Дата окончания</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Модальные окна -->
    <!-- Пользователи -->
    <div id="edit-user-modal" class="modal">
        <div class="modal-content">
            <h3>Редактировать пользователя</h3>
            <form id="edit-user-form">
                <input type="hidden" name="user_id">
                <label>Имя:</label>
                <input type="text" name="name" required>
                <div class="error" id="edit-user-name-error">Пожалуйста, введите имя</div>
                <label>Email:</label>
                <input type="email" name="email" required>
                <div class="error" id="edit-user-email-error">Пожалуйста, введите корректный email</div>
                <label>Роль:</label>
                <select name="role_id" required></select>
                <div class="error" id="edit-user-role-error">Пожалуйста, выберите роль</div>
                <label>Телефон:</label>
                <input type="text" name="phone">
                <label>Почтовый индекс:</label>
                <input type="text" name="postal_code">
                <label>Предпочитаемый способ оплаты:</label>
                <input type="text" name="preferred_payment_method">
                <label>Предпочитаемый способ доставки:</label>
                <input type="text" name="preferred_delivery_method">
                <div class="buttons">
                    <button type="submit">Сохранить</button>
                    <button type="button" onclick="closeModal('edit-user-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Заказы -->
    <div id="edit-order-modal" class="modal">
        <div class="modal-content">
            <h3>Редактировать заказ</h3>
            <form id="edit-order-form">
                <input type="hidden" name="order_id">
                <label>Статус заказа:</label>
                <select name="order_status" required>
                    <option value="Новый">Новый</option>
                    <option value="В обработке">В обработке</option>
                    <option value="Доставлен">Доставлен</option>
                    <option value="Отменен">Отменен</option>
                    <option value="Отправлен">Отправлен</option>
                    <option value="В ожидании">В ожидании</option>
                </select>
                <label>Адрес доставки:</label>
                <textarea name="delivery_address"></textarea>
                <div id="order-items"></div>
                <div class="buttons">
                    <button type="submit">Сохранить</button>
                    <button type="button" onclick="closeModal('edit-order-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Продукты -->
    <div id="add-product-modal" class="modal">
        <div class="modal-content">
            <h3>Добавить продукт</h3>
            <form id="add-product-form" enctype="multipart/form-data">
                <label>Название:</label>
                <input type="text" name="name" required>
                <div class="error" id="add-product-name-error">Пожалуйста, введите название</div>
                <label>Описание:</label>
                <textarea name="description"></textarea>
                <label>Цена:</label>
                <input type="number" name="price" step="0.01" min="0" required>
                <div class="error" id="add-product-price-error">Пожалуйста, введите корректную цену</div>
                <label>Количество на складе:</label>
                <input type="number" name="stock_quantity" min="0" required>
                <div class="error" id="add-product-stock-error">Пожалуйста, введите количество</div>
                <label>Скидка (%):</label>
                <input type="number" name="discount" step="0.01" min="0" max="100">
                <label>Категория:</label>
                <select name="category_id" onchange="loadCharacteristics(this.value)"></select>
                <label>Изображение:</label>
                <input type="file" name="image" accept="image/*">
                <label>Бестселлер:</label>
                <input type="checkbox" name="is_bestseller">
                <label>Новый:</label>
                <input type="checkbox" name="is_new">
                <div id="characteristics-container"></div>
                <div class="buttons">
                    <button type="submit">Добавить</button>
                    <button type="button" onclick="closeModal('add-product-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    <div id="edit-product-modal" class="modal">
        <div class="modal-content">
            <h3>Редактировать продукт</h3>
            <form id="edit-product-form" enctype="multipart/form-data">
                <input type="hidden" name="product_id">
                <label>Название:</label>
                <input type="text" name="name" required>
                <div class="error" id="edit-product-name-error">Пожалуйста, введите название</div>
                <label>Описание:</label>
                <textarea name="description"></textarea>
                <label>Цена:</label>
                <input type="number" name="price" step="0.01" min="0" required>
                <div class="error" id="edit-product-price-error">Пожалуйста, введите корректную цену</div>
                <label>Количество на складе:</label>
                <input type="number" name="stock_quantity" min="0" required>
                <div class="error" id="edit-product-stock-error">Пожалуйста, введите количество</div>
                <label>Скидка (%):</label>
                <input type="number" name="discount" step="0.01" min="0" max="100">
                <label>Категория:</label>
                <select name="category_id" onchange="loadCharacteristics(this.value)"></select>
                <label>Изображение:</label>
                <input type="file" name="image" accept="image/*">
                <div id="current-image"></div>
                <label>Бестселлер:</label>
                <input type="checkbox" name="is_bestseller">
                <label>Новый:</label>
                <input type="checkbox" name="is_new">
                <div id="edit-characteristics-container"></div>
                <div class="buttons">
                    <button type="submit">Сохранить</button>
                    <button type="button" onclick="closeModal('edit-product-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Категории -->
    <div id="add-category-modal" class="modal">
        <div class="modal-content">
            <h3>Добавить категорию</h3>
            <form id="add-category-form">
                <label>Название:</label>
                <input type="text" name="name" required>
                <div class="buttons">
                    <button type="submit">Добавить</button>
                    <button type="button" onclick="closeModal('add-category-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    <div id="edit-category-modal" class="modal">
        <div class="modal-content">
            <h3>Редактировать категорию</h3>
            <form id="edit-category-form">
                <input type="hidden" name="category_id">
                <label>Название:</label>
                <input type="text" name="name" required>
                <div class="buttons">
                    <button type="submit">Сохранить</button>
                    <button type="button" onclick="closeModal('edit-category-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Характеристики -->
    <div id="add-characteristic-modal" class="modal">
        <div class="modal-content">
            <h3>Добавить характеристику</h3>
            <form id="add-characteristic-form">
                <label>Название:</label>
                <input type="text" name="name" required>
                <label>Тип значения:</label>
                <select name="value_type" required>
                    <option value="Текст">Текст</option>
                    <option value="Число">Число</option>
                </select>
                <label>Категория:</label>
                <select name="category_id" required></select>
                <div class="buttons">
                    <button type="submit">Добавить</button>
                    <button type="button" onclick="closeModal('add-characteristic-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    <div id="edit-characteristic-modal" class="modal">
        <div class="modal-content">
            <h3>Редактировать характеристику</h3>
            <form id="edit-characteristic-form">
                <input type="hidden" name="characteristic_id">
                <label>Название:</label>
                <input type="text" name="name" required>
                <label>Тип значения:</label>
                <select name="value_type" required>
                    <option value="Текст">Текст</option>
                    <option value="Число">Число</option>
                </select>
                <label>Категория:</label>
                <select name="category_id" required></select>
                <div class="buttons">
                    <button type="submit">Сохранить</button>
                    <button type="button" onclick="closeModal('edit-characteristic-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Новости -->
    <div id="add-news-modal" class="modal">
        <div class="modal-content">
            <h3>Добавить новость</h3>
            <form id="add-news-form" enctype="multipart/form-data">
                <label>Заголовок:</label>
                <input type="text" name="title" required>
                <label>Содержание:</label>
                <textarea name="content" required></textarea>
                <label>Изображение:</label>
                <input type="file" name="image" accept="image/*">
                <div class="buttons">
                    <button type="submit">Добавить</button>
                    <button type="button" onclick="closeModal('add-news-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    <div id="edit-news-modal" class="modal">
        <div class="modal-content">
            <h3>Редактировать новость</h3>
            <form id="edit-news-form" enctype="multipart/form-data">
                <input type="hidden" name="news_id">
                <label>Заголовок:</label>
                <input type="text" name="title" required>
                <label>Содержание:</label>
                <textarea name="content" required></textarea>
                <label>Изображение:</label>
                <input type="file" name="image" accept="image/*">
                <div id="current-news-image"></div>
                <div class="buttons">
                    <button type="submit">Сохранить</button>
                    <button type="button" onclick="closeModal('edit-news-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Акции -->
    <div id="add-promotion-modal" class="modal">
        <div class="modal-content">
            <h3>Добавить акцию</h3>
            <form id="add-promotion-form" enctype="multipart/form-data">
                <label>Заголовок:</label>
                <input type="text" name="title" required>
                <label>Описание:</label>
                <textarea name="description"></textarea>
                <label>Дата начала:</label>
                <input type="date" name="start_date" required>
                <label>Дата окончания:</label>
                <input type="date" name="end_date" required>
                <label>Категория:</label>
                <select name="category_id"></select>
                <label>Изображение:</label>
                <input type="file" name="image" accept="image/*">
                <div class="buttons">
                    <button type="submit">Добавить</button>
                    <button type="button" onclick="closeModal('add-promotion-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    <div id="edit-promotion-modal" class="modal">
        <div class="modal-content">
            <h3>Редактировать акцию</h3>
            <form id="edit-promotion-form" enctype="multipart/form-data">
                <input type="hidden" name="promotion_id">
                <label>Заголовок:</label>
                <input type="text" name="title" required>
                <label>Описание:</label>
                <textarea name="description"></textarea>
                <label>Дата начала:</label>
                <input type="date" name="start_date" required>
                <label>Дата окончания:</label>
                <input type="date" name="end_date" required>
                <label>Категория:</label>
                <select name="category_id"></select>
                <label>Изображение:</label>
                <input type="file" name="image" accept="image/*">
                <div id="current-promotion-image"></div>
                <div class="buttons">
                    <button type="submit">Сохранить</button>
                    <button type="button" onclick="closeModal('edit-promotion-modal')">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const API_URL = '/brow12/api/admin_api.php';

        document.addEventListener('DOMContentLoaded', () => {
            const navLinks = document.querySelectorAll('.sidebar nav ul li a');
            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const section = link.getAttribute('data-section');
                    showSection(section);
                    loadData(section);
                });
            });

            fetch(`${API_URL}?action=get_admin_info`, { credentials: 'include' })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('admin-name').textContent = data.data.admin_name;
                    }
                })
                .catch(error => console.error('Ошибка:', error));

            showSection('analytics');
            loadData('analytics');
        });

        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => section.classList.add('hidden'));
            document.getElementById(sectionId).classList.remove('hidden');
        }

        async function loadData(type, search = '') {
    try {
        let url = `${API_URL}?action=get_${type}&search=${encodeURIComponent(search)}`;
        if (type === 'analytics') url = `${API_URL}?action=get_analytics`;
        const response = await fetch(url, { credentials: 'include' });
        const data = await response.json();
        if (data.status === 'success') {
            switch (type) {
                case 'analytics':
                    document.getElementById('total-users').textContent = data.data.total_users;
                    document.getElementById('total-orders').textContent = data.data.total_orders;
                    document.getElementById('total-products').textContent = data.data.total_products;
                    document.getElementById('total-sales').textContent = data.data.total_sales;
                    break;
                case 'users':
                    const usersTbody = document.querySelector('#users-table tbody');
                    usersTbody.innerHTML = '';
                    if (data.data.length === 0) {
                        usersTbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Пользователи не найдены</td></tr>';
                    } else {
                        data.data.forEach(user => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${user.user_id}</td>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td>${user.role_name}</td>
                                <td>${user.phone || ''}</td>
                                <td>${user.status}</td>
                                <td>${new Date(user.created_at).toLocaleString()}</td>
                                <td>
                                    <button onclick='editUser(${JSON.stringify(user)})'>Редактировать</button>
                                    <button onclick="deleteUser(${user.user_id})">Удалить</button>
                                </td>
                            `;
                            usersTbody.appendChild(row);
                        });
                    }
                    break;
                case 'orders':
                    const ordersTbody = document.querySelector('#orders-table tbody');
                    ordersTbody.innerHTML = '';
                    if (data.data.length === 0) {
                        ordersTbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Заказы не найдены</td></tr>';
                    } else {
                        data.data.forEach(order => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${order.order_id}</td>
                                <td>${order.order_code}</td>
                                <td>${order.user_name} (${order.user_email})</td>
                                <td>${order.order_status}</td>
                                <td>${order.total_price}</td>
                                <td>${order.delivery_address}</td>
                                <td>${new Date(order.created_at).toLocaleString()}</td>
                                <td>
                                    <button onclick='editOrder(${JSON.stringify(order)})'>Редактировать</button>
                                    <button onclick="deleteOrder(${order.order_id})">Удалить</button>
                                </td>
                            `;
                            ordersTbody.appendChild(row);
                        });
                    }
                    break;
                case 'products':
                    const productsTbody = document.querySelector('#products-table tbody');
                    productsTbody.innerHTML = '';
                    if (data.data.length === 0) {
                        productsTbody.innerHTML = '<tr><td colspan="11" style="text-align: center;">Продукты не найдены</td></tr>';
                    } else {
                        data.data.forEach(product => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${product.product_id}</td>
                                <td>${product.image_url ? `<img src="/brow12/${product.image_url}" alt="${product.name}" class="product-image">` : 'Нет'}</td>
                                <td>${product.name}</td>
                                <td>${product.category_name || 'Без категории'}</td>
                                <td>${product.price}</td>
                                <td>${product.discount}</td>
                                <td>${product.stock_quantity}</td>
                                <td>${product.is_bestseller ? 'Да' : 'Нет'}</td>
                                <td>${product.is_new ? 'Да' : 'Нет'}</td>
                                <td>${new Date(product.created_at).toLocaleString()}</td>
                                <td>
                                    <button onclick='editProduct(${JSON.stringify(product)})'>Редактировать</button>
                                    <button onclick="deleteProduct(${product.product_id})">Удалить</button>
                                </td>
                            `;
                            productsTbody.appendChild(row);
                        });
                    }
                    break;
                case 'categories':
                    const categoriesTbody = document.querySelector('#categories-table tbody');
                    categoriesTbody.innerHTML = '';
                    if (data.data.length === 0) {
                        categoriesTbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Категории не найдены</td></tr>';
                    } else {
                        data.data.forEach(category => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${category.category_id}</td>
                                <td>${category.name}</td>
                                <td>${category.parent_name || 'Нет'}</td>
                                <td>
                                    <button onclick='editCategory(${JSON.stringify(category)})'>Редактировать</button>
                                    <button onclick="deleteCategory(${category.category_id})">Удалить</button>
                                </td>
                            `;
                            categoriesTbody.appendChild(row);
                        });
                    }
                    break;
                case 'characteristics':
                    const characteristicsTbody = document.querySelector('#characteristics-table tbody');
                    characteristicsTbody.innerHTML = '';
                    if (data.data.length === 0) {
                        characteristicsTbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Характеристики не найдены</td></tr>';
                    } else {
                        data.data.forEach(char => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${char.characteristic_id}</td>
                                <td>${char.name}</td>
                                <td>${char.value_type}</td>
                                <td>${char.category_name || 'Без категории'}</td>
                                <td>
                                    <button onclick='editCharacteristic(${JSON.stringify(char)})'>Редактировать</button>
                                    <button onclick="deleteCharacteristic(${char.characteristic_id})">Удалить</button>
                                </td>
                            `;
                            characteristicsTbody.appendChild(row);
                        });
                    }
                    break;
                case 'news':
                    const newsTbody = document.querySelector('#news-table tbody');
                    newsTbody.innerHTML = '';
                    if (data.data.length === 0) {
                        newsTbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Новости не найдены</td></tr>';
                    } else {
                        data.data.forEach(news => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${news.news_id}</td>
                                <td>${news.title}</td>
                                <td>${news.image_url ? `<img src="/brow12/${news.image_url}" alt="${news.title}" class="product-image">` : 'Нет'}</td>
                                <td>${new Date(news.created_at).toLocaleString()}</td>
                                <td>
                                    <button onclick='editNews(${JSON.stringify(news)})'>Редактировать</button>
                                    <button onclick="deleteNews(${news.news_id})">Удалить</button>
                                </td>
                            `;
                            newsTbody.appendChild(row);
                        });
                    }
                    break;
                case 'promotions':
                    const promotionsTbody = document.querySelector('#promotions-table tbody');
                    promotionsTbody.innerHTML = '';
                    if (data.data.length === 0) {
                        promotionsTbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Акции не найдены</td></tr>';
                    } else {
                        data.data.forEach(promo => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${promo.promotion_id}</td>
                                <td>${promo.title}</td>
                                <td>${promo.image_url ? `<img src="/brow12/${promo.image_url}" alt="${promo.title}" class="product-image">` : 'Нет'}</td>
                                <td>${promo.category_name || 'Без категории'}</td>
                                <td>${new Date(promo.start_date).toLocaleDateString()}</td>
                                <td>${new Date(promo.end_date).toLocaleDateString()}</td>
                                <td>${promo.status}</td>
                                <td>
                                    <button onclick='editPromotion(${JSON.stringify(promo)})'>Редактировать</button>
                                    <button onclick="deletePromotion(${promo.promotion_id})">Удалить</button>
                                </td>
                            `;
                            promotionsTbody.appendChild(row);
                        });
                    }
                    break;
            }
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при загрузке данных');
    }
}

        async function populateCategorySelect(select, selectedId = '') {
            const response = await fetch(`${API_URL}?action=get_categories`, { credentials: 'include' });
            const data = await response.json();
            if (data.status === 'success') {
                select.innerHTML = '<option value="">Без категории</option>';
                data.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.category_id;
                    option.textContent = category.name + (category.parent_name ? ` (${category.parent_name})` : '');
                    if (category.category_id == selectedId) option.selected = true;
                    select.appendChild(option);
                });
            } else {
                alert(data.message);
            }
        }

        async function populateRoleSelect(select, selectedId) {
            const response = await fetch(`${API_URL}?action=get_roles`, { credentials: 'include' });
            const data = await response.json();
            if (data.status === 'success') {
                select.innerHTML = '';
                data.data.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.id;
                    option.textContent = role.name;
                    if (role.id == selectedId) option.selected = true;
                    select.appendChild(option);
                });
            } else {
                alert(data.message);
            }
        }

        async function loadCharacteristics(categoryId, productId = null) {
            const container = productId ? document.getElementById('edit-characteristics-container') : document.getElementById('characteristics-container');
            container.innerHTML = '';
            if (!categoryId) return;
            const response = await fetch(`${API_URL}?action=get_characteristics&category_id=${categoryId}`, { credentials: 'include' });
            const data = await response.json();
            if (data.status === 'success') {
                let existingCharacteristics = {};
                if (productId) {
                    const productResponse = await fetch(`${API_URL}?action=get_product_characteristics&product_id=${productId}`, { credentials: 'include' });
                    const productData = await productResponse.json();
                    if (productData.status === 'success') {
                        productData.data.forEach(char => existingCharacteristics[char.characteristic_id] = char.value);
                    }
                }
                data.data.forEach(char => {
                    const value = existingCharacteristics[char.characteristic_id] || '';
                    const div = document.createElement('div');
                    const inputType = char.value_type === 'Число' ? 'number' : 'text';
                    div.innerHTML = `
                        <label>${char.name} (${char.value_type}):</label>
                        <input type="${inputType}" name="characteristic_${char.characteristic_id}" value="${value}">
                    `;
                    container.appendChild(div);
                });
            } else {
                alert(data.message);
            }
        }

        // Валидация форм
        function validateProductForm(form, prefix) {
            let isValid = true;
            const name = form.name.value.trim();
            const price = form.price.value;
            const stock = form.stock_quantity.value;
            const discount = form.discount.value;

            document.getElementById(`${prefix}-name-error`).style.display = 'none';
            document.getElementById(`${prefix}-price-error`).style.display = 'none';
            document.getElementById(`${prefix}-stock-error`).style.display = 'none';

            if (!name) {
                document.getElementById(`${prefix}-name-error`).style.display = 'block';
                isValid = false;
            }
            if (price < 0 || price === '') {
                document.getElementById(`${prefix}-price-error`).style.display = 'block';
                isValid = false;
            }
            if (stock < 0 || stock === '') {
                document.getElementById(`${prefix}-stock-error`).style.display = 'block';
                isValid = false;
            }
            return isValid;
        }

        function validateUserForm(form) {
            let isValid = true;
            const name = form.name.value.trim();
            const email = form.email.value.trim();
            const role = form.role_id.value;

            document.getElementById('edit-user-name-error').style.display = 'none';
            document.getElementById('edit-user-email-error').style.display = 'none';
            document.getElementById('edit-user-role-error').style.display = 'none';

            if (!name) {
                document.getElementById('edit-user-name-error').style.display = 'block';
                isValid = false;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('edit-user-email-error').style.display = 'block';
                isValid = false;
            }
            if (!role) {
                document.getElementById('edit-user-role-error').style.display = 'block';
                isValid = false;
            }
            return isValid;
        }

        // Продукты
        function openAddProductModal() {
            const modal = document.getElementById('add-product-modal');
            const form = document.getElementById('add-product-form');
            form.reset();
            populateCategorySelect(form.querySelector('select[name="category_id"]'));
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                if (!validateProductForm(form, 'add-product')) return;
                const formData = new FormData(form);
                formData.append('action', 'add_product');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('add-product-modal');
                    loadData('products');
                } else {
                    alert(data.message);
                }
            };
        }

        function editProduct(product) {
            const modal = document.getElementById('edit-product-modal');
            const form = document.getElementById('edit-product-form');
            form.product_id.value = product.product_id;
            form.name.value = product.name;
            form.description.value = product.description || '';
            form.price.value = product.price;
            form.stock_quantity.value = product.stock_quantity;
            form.discount.value = product.discount || '';
            form.is_bestseller.checked = product.is_bestseller;
            form.is_new.checked = product.is_new;
            const currentImageDiv = document.getElementById('current-image');
            currentImageDiv.innerHTML = product.image_url ? `<img src="/brow12/${product.image_url}" alt="Current Image" style="max-width: 100px;">` : 'Нет изображения';
            populateCategorySelect(form.querySelector('select[name="category_id"]'), product.category_id);
            loadCharacteristics(product.category_id, product.product_id);
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                if (!validateProductForm(form, 'edit-product')) return;
                const formData = new FormData(form);
                formData.append('action', 'edit_product');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('edit-product-modal');
                    loadData('products');
                } else {
                    alert(data.message);
                }
            };
        }

        async function deleteProduct(productId) {
            if (!confirm('Вы уверены, что хотите удалить этот продукт?')) return;
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_product&product_id=${productId}`,
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') loadData('products');
            else alert(data.message);
        }

        // Пользователи
        function editUser(user) {
            const modal = document.getElementById('edit-user-modal');
            const form = document.getElementById('edit-user-form');
            form.user_id.value = user.user_id;
            form.name.value = user.name;
            form.email.value = user.email;
            form.phone.value = user.phone || '';
            form.postal_code.value = user.postal_code || '';
            form.preferred_payment_method.value = user.preferred_payment_method || '';
            form.preferred_delivery_method.value = user.preferred_delivery_method || '';
            populateRoleSelect(form.querySelector('select[name="role_id"]'), user.role_id);
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                if (!validateUserForm(form)) return;
                const formData = new FormData(form);
                formData.append('action', 'edit_user');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('edit-user-modal');
                    loadData('users');
                } else {
                    alert(data.message);
                }
            };
        }

        async function deleteUser(userId) {
            if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) return;
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_user&user_id=${userId}`,
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') loadData('users');
            else alert(data.message);
        }

        // Заказы
        function editOrder(order) {
            const modal = document.getElementById('edit-order-modal');
            const form = document.getElementById('edit-order-form');
            form.order_id.value = order.order_id;
            form.order_status.value = order.order_status;
            form.delivery_address.value = order.delivery_address;
            const itemsDiv = document.getElementById('order-items');
            itemsDiv.innerHTML = '<h4>Товары:</h4>' + order.items.map(item => `<p>${item.product_name} - ${item.quantity} шт. по ${item.price_per_item} руб.</p>`).join('');
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'edit_order');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('edit-order-modal');
                    loadData('orders');
                } else {
                    alert(data.message);
                }
            };
        }

        async function deleteOrder(orderId) {
            if (!confirm('Вы уверены, что хотите удалить этот заказ?')) return;
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_order&order_id=${orderId}`,
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') loadData('orders');
            else alert(data.message);
        }

        // Категории
        function openAddCategoryModal() {
            const modal = document.getElementById('add-category-modal');
            const form = document.getElementById('add-category-form');
            form.reset();
            populateCategorySelect(form.querySelector('select[name="parent_category_id"]'));
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'add_category');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('add-category-modal');
                    loadData('categories');
                } else {
                    alert(data.message);
                }
            };
        }

        function editCategory(category) {
            const modal = document.getElementById('edit-category-modal');
            const form = document.getElementById('edit-category-form');
            form.category_id.value = category.category_id;
            form.name.value = category.name;
            populateCategorySelect(form.querySelector('select[name="parent_category_id"]'), category.parent_category_id);
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'edit_category');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('edit-category-modal');
                    loadData('categories');
                } else {
                    alert(data.message);
                }
            };
        }

        async function deleteCategory(categoryId) {
            if (!confirm('Вы уверены, что хотите удалить эту категорию?')) return;
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_category&category_id=${categoryId}`,
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') loadData('categories');
            else alert(data.message);
        }

        // Характеристики
        function openAddCharacteristicModal() {
            const modal = document.getElementById('add-characteristic-modal');
            const form = document.getElementById('add-characteristic-form');
            form.reset();
            populateCategorySelect(form.querySelector('select[name="category_id"]'));
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'add_characteristic');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('add-characteristic-modal');
                    loadData('characteristics');
                } else {
                    alert(data.message);
                }
            };
        }

        function editCharacteristic(char) {
            const modal = document.getElementById('edit-characteristic-modal');
            const form = document.getElementById('edit-characteristic-form');
            form.characteristic_id.value = char.characteristic_id;
            form.name.value = char.name;
            form.value_type.value = char.value_type;
            populateCategorySelect(form.querySelector('select[name="category_id"]'), char.category_id);
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'edit_characteristic');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('edit-characteristic-modal');
                    loadData('characteristics');
                } else {
                    alert(data.message);
                }
            };
        }

        async function deleteCharacteristic(charId) {
            if (!confirm('Вы уверены, что хотите удалить эту характеристику?')) return;
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_characteristic&characteristic_id=${charId}`,
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') loadData('characteristics');
            else alert(data.message);
        }

        // Новости
        function openAddNewsModal() {
            const modal = document.getElementById('add-news-modal');
            const form = document.getElementById('add-news-form');
            form.reset();
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'add_news');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('add-news-modal');
                    loadData('news');
                } else {
                    alert(data.message);
                }
            };
        }

        function editNews(news) {
            const modal = document.getElementById('edit-news-modal');
            const form = document.getElementById('edit-news-form');
            form.news_id.value = news.news_id;
            form.title.value = news.title;
            form.content.value = news.content;
            const currentImageDiv = document.getElementById('current-news-image');
            currentImageDiv.innerHTML = news.image_url ? `<img src="/brow12/${news.image_url}" alt="Current Image" style="max-width: 100px;">` : 'Нет изображения';
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'edit_news');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('edit-news-modal');
                    loadData('news');
                } else {
                    alert(data.message);
                }
            };
        }

        async function deleteNews(newsId) {
            if (!confirm('Вы уверены, что хотите удалить эту новость?')) return;
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_news&news_id=${newsId}`,
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') loadData('news');
            else alert(data.message);
        }

        // Акции
        function openAddPromotionModal() {
            const modal = document.getElementById('add-promotion-modal');
            const form = document.getElementById('add-promotion-form');
            form.reset();
            populateCategorySelect(form.querySelector('select[name="category_id"]'));
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'add_promotion');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('add-promotion-modal');
                    loadData('promotions');
                } else {
                    alert(data.message);
                }
            };
        }

        function editPromotion(promo) {
            const modal = document.getElementById('edit-promotion-modal');
            const form = document.getElementById('edit-promotion-form');
            form.promotion_id.value = promo.promotion_id;
            form.title.value = promo.title;
            form.description.value = promo.description || '';
            form.start_date.value = promo.start_date.split(' ')[0];
            form.end_date.value = promo.end_date.split(' ')[0];
            populateCategorySelect(form.querySelector('select[name="category_id"]'), promo.category_id);
            const currentImageDiv = document.getElementById('current-promotion-image');
            currentImageDiv.innerHTML = promo.image_url ? `<img src="/brow12/${promo.image_url}" alt="Current Image" style="max-width: 100px;">` : 'Нет изображения';
            modal.style.display = 'flex';
            form.onsubmit = async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'edit_promotion');
                const response = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal('edit-promotion-modal');
                    loadData('promotions');
                } else {
                    alert(data.message);
                }
            };
        }

        async function deletePromotion(promoId) {
            if (!confirm('Вы уверены, что хотите удалить эту акцию?')) return;
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_promotion&promotion_id=${promoId}`,
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') loadData('promotions');
            else alert(data.message);
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
    </script>
</body>
</html>