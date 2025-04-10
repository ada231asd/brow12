
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
        </div>
    </div>

    <!-- Модальное окно для редактирования пользователя -->
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
                <select name="role_id" required>
                    <!-- Заполняется динамически -->
                </select>
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

    <!-- Модальное окно для редактирования заказа -->
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

    <!-- Модальное окно для добавления продукта -->
    <div id="add-product-modal" class="modal">
        <div class="modal-content">
            <h3>Добавить продукт</h3>
            <form id="add-product-form" enctype="multipart/form-data">
                <label>Название:</label>
                <input type="text" name="name" required>
                <div class="error" id="add-product-name-error">Пожалуйста, введите название продукта</div>
                <label>Описание:</label>
                <textarea name="description"></textarea>
                <label>Цена:</label>
                <input type="number" name="price" step="0.01" min="0" required>
                <div class="error" id="add-product-price-error">Пожалуйста, введите корректную цену (больше или равно 0)</div>
                <label>Количество на складе:</label>
                <input type="number" name="stock_quantity" min="0" required>
                <div class="error" id="add-product-stock-error">Пожалуйста, введите количество (больше или равно 0)</div>
                <label>Скидка (%):</label>
                <input type="number" name="discount" step="0.01" min="0" max="100">
                <div class="error" id="add-product-discount-error">Скидка должна быть от 0 до 100</div>
                <label>Категория:</label>
                <select name="category_id" onchange="loadCharacteristics(this.value)">
                    <!-- Заполняется динамически -->
                </select>
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

    <!-- Модальное окно для редактирования продукта -->
    <div id="edit-product-modal" class="modal">
        <div class="modal-content">
            <h3>Редактировать продукт</h3>
            <form id="edit-product-form" enctype="multipart/form-data">
                <input type="hidden" name="product_id">
                <label>Название:</label>
                <input type="text" name="name" required>
                <div class="error" id="edit-product-name-error">Пожалуйста, введите название продукта</div>
                <label>Описание:</label>
                <textarea name="description"></textarea>
                <label>Цена:</label>
                <input type="number" name="price" step="0.01" min="0" required>
                <div class="error" id="edit-product-price-error">Пожалуйста, введите корректную цену (больше или равно 0)</div>
                <label>Количество на складе:</label>
                <input type="number" name="stock_quantity" min="0" required>
                <div class="error" id="edit-product-stock-error">Пожалуйста, введите количество (больше или равно 0)</div>
                <label>Скидка (%):</label>
                <input type="number" name="discount" step="0.01" min="0" max="100">
                <div class="error" id="edit-product-discount-error">Скидка должна быть от 0 до 100</div>
                <label>Категория:</label>
                <select name="category_id" onchange="loadCharacteristics(this.value)">
                    <!-- Заполняется динамически -->
                </select>
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

    <script>
        const API_URL = '/../brow12/api/admin_api.php';

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

            // Загрузка имени администратора
            fetch(`${API_URL}?action=get_admin_info`, {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('admin-name').textContent = data.data.admin_name;
                }
            })
            .catch(error => console.error('Ошибка:', error));

            // Показываем аналитику по умолчанию
            showSection('analytics');
            loadData('analytics');
        });

        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.add('hidden');
            });
            document.getElementById(sectionId).classList.remove('hidden');
        }

        async function loadData(type, search = '') {
            try {
                let url = `${API_URL}?action=get_${type}&search=${encodeURIComponent(search)}`;
                if (type === 'analytics') {
                    url = `${API_URL}?action=get_analytics`;
                }
                const response = await fetch(url, {
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.status === 'success') {
                    if (type === 'analytics') {
                        document.getElementById('total-users').textContent = data.data.total_users;
                        document.getElementById('total-orders').textContent = data.data.total_orders;
                        document.getElementById('total-products').textContent = data.data.total_products;
                        document.getElementById('total-sales').textContent = data.data.total_sales;
                    } else if (type === 'users') {
                        const tbody = document.querySelector('#users-table tbody');
                        tbody.innerHTML = '';
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
                            tbody.appendChild(row);
                        });
                    } else if (type === 'orders') {
                        const tbody = document.querySelector('#orders-table tbody');
                        tbody.innerHTML = '';
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
                            tbody.appendChild(row);
                        });
                    } else if (type === 'products') {
                        const tbody = document.querySelector('#products-table tbody');
                        tbody.innerHTML = '';
                        data.data.forEach(product => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${product.product_id}</td>
                                <td>${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" class="product-image">` : 'Нет изображения'}</td>
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
                            tbody.appendChild(row);
                        });
                    }
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при загрузке данных');
            }
        }

        async function populateCategorySelect(select, selectedId) {
            try {
                const response = await fetch(`${API_URL}?action=get_categories`, {
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.status === 'success') {
                    select.innerHTML = '<option value="">Выберите категорию</option>';
                    data.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.category_id;
                        option.textContent = category.name + (category.parent_name ? ` (${category.parent_name})` : '');
                        if (category.category_id == selectedId) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при загрузке категорий');
            }
        }

        async function populateRoleSelect(select, selectedId) {
            try {
                const response = await fetch(`${API_URL}?action=get_roles`, {
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.status === 'success') {
                    select.innerHTML = '';
                    data.data.forEach(role => {
                        const option = document.createElement('option');
                        option.value = role.id;
                        option.textContent = role.name;
                        if (role.id == selectedId) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при загрузке ролей');
            }
        }

        async function loadCharacteristics(categoryId, productId = null) {
            const container = productId ? document.getElementById('edit-characteristics-container') : document.getElementById('characteristics-container');
            container.innerHTML = '';

            if (!categoryId) return;

            try {
                const response = await fetch(`${API_URL}?action=get_characteristics&category_id=${categoryId}`, {
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.status === 'success') {
                    let existingCharacteristics = {};
                    if (productId) {
                        const productResponse = await fetch(`${API_URL}?action=get_product_characteristics&product_id=${productId}`, {
                            credentials: 'include'
                        });
                        const productData = await productResponse.json();
                        if (productData.status === 'success') {
                            productData.data.forEach(char => {
                                existingCharacteristics[char.characteristic_id] = char.value;
                            });
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
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при загрузке характеристик');
            }
        }

        function validateAddProductForm(form) {
            let isValid = true;
            const name = form.name.value.trim();
            const price = form.price.value;
            const stock = form.stock_quantity.value;
            const discount = form.discount.value;

            // Сброс ошибок
            document.getElementById('add-product-name-error').style.display = 'none';
            document.getElementById('add-product-price-error').style.display = 'none';
            document.getElementById('add-product-stock-error').style.display = 'none';
            document.getElementById('add-product-discount-error').style.display = 'none';

            if (!name) {
                document.getElementById('add-product-name-error').style.display = 'block';
                isValid = false;
            }
            if (price < 0 || price === '') {
                document.getElementById('add-product-price-error').style.display = 'block';
                isValid = false;
            }
            if (stock < 0 || stock === '') {
                document.getElementById('add-product-stock-error').style.display = 'block';
                isValid = false;
            }
            if (discount !== '' && (discount < 0 || discount > 100)) {
                document.getElementById('add-product-discount-error').style.display = 'block';
                isValid = false;
            }

            return isValid;
        }

        function validateEditProductForm(form) {
            let isValid = true;
            const name = form.name.value.trim();
            const price = form.price.value;
            const stock = form.stock_quantity.value;
            const discount = form.discount.value;

            // Сброс ошибок
            document.getElementById('edit-product-name-error').style.display = 'none';
            document.getElementById('edit-product-price-error').style.display = 'none';
            document.getElementById('edit-product-stock-error').style.display = 'none';
            document.getElementById('edit-product-discount-error').style.display = 'none';

            if (!name) {
                document.getElementById('edit-product-name-error').style.display = 'block';
                isValid = false;
            }
            if (price < 0 || price === '') {
                document.getElementById('edit-product-price-error').style.display = 'block';
                isValid = false;
            }
            if (stock < 0 || stock === '') {
                document.getElementById('edit-product-stock-error').style.display = 'block';
                isValid = false;
            }
            if (discount !== '' && (discount < 0 || discount > 100)) {
                document.getElementById('edit-product-discount-error').style.display = 'block';
                isValid = false;
            }

            return isValid;
        }

        function validateEditUserForm(form) {
            let isValid = true;
            const name = form.name.value.trim();
            const email = form.email.value.trim();
            const role = form.role_id.value;

            // Сброс ошибок
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

        function validateEditOrderForm(form) {
            let isValid = true;
            const status = form.order_status.value;

            // Сброс ошибок
            document.getElementById('edit-order-status-error').style.display = 'none';

            if (!status) {
                document.getElementById('edit-order-status-error').style.display = 'block';
                isValid = false;
            }

            return isValid;
        }

        function openAddProductModal() {
            const modal = document.getElementById('add-product-modal');
            const form = document.getElementById('add-product-form');
            form.reset();

            const categorySelect = form.querySelector('select[name="category_id"]');
            populateCategorySelect(categorySelect, '');

            modal.style.display = 'flex';

            form.onsubmit = async (e) => {
                e.preventDefault();
                if (!validateAddProductForm(form)) return;

                const formData = new FormData(form);
                formData.append('action', 'add_product');
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        closeModal('add-product-modal');
                        loadData('products', document.getElementById('product-search').value);
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при добавлении продукта');
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
            form.discount.value = product.discount;
            form.is_bestseller.checked = product.is_bestseller;
            form.is_new.checked = product.is_new;

            const currentImageDiv = document.getElementById('current-image');
            if (product.image_url) {
                currentImageDiv.innerHTML = `<p>Текущее изображение: <img src="${product.image_url}" alt="Product Image" style="max-width: 100px;"></p>`;
            } else {
                currentImageDiv.innerHTML = '<p>Изображение отсутствует</p>';
            }

            const categorySelect = form.querySelector('select[name="category_id"]');
            populateCategorySelect(categorySelect, product.category_id);
            loadCharacteristics(product.category_id, product.product_id);

            modal.style.display = 'flex';

            form.onsubmit = async (e) => {
                e.preventDefault();
                if (!validateEditProductForm(form)) return;

                const formData = new FormData(form);
                formData.append('action', 'edit_product');
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        closeModal('edit-product-modal');
                        loadData('products', document.getElementById('product-search').value);
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при редактировании продукта');
                }
            };
        }

        async function deleteProduct(productId) {
            if (!confirm('Вы уверены, что хотите удалить этот продукт?')) return;
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_product&product_id=${productId}`,
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.status === 'success') {
                    loadData('products', document.getElementById('product-search').value);
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при удалении продукта');
            }
        }

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

            const roleSelect = form.querySelector('select[name="role_id"]');
            populateRoleSelect(roleSelect, user.role_id);

            modal.style.display = 'flex';

            form.onsubmit = async (e) => {
                e.preventDefault();
                if (!validateEditUserForm(form)) return;

                const formData = new FormData(form);
                formData.append('action', 'edit_user');
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        closeModal('edit-user-modal');
                        loadData('users', document.getElementById('user-search').value);
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при редактировании пользователя');
                }
            };
        }

        async function deleteUser(userId) {
            if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) return;
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_user&user_id=${userId}`,
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.status === 'success') {
                    loadData('users', document.getElementById('user-search').value);
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при удалении пользователя');
            }
        }

        function editOrder(order) {
            const modal = document.getElementById('edit-order-modal');
            const form = document.getElementById('edit-order-form');
            form.order_id.value = order.order_id;
            form.order_status.value = order.order_status;
        
            form.delivery_address.value = order.delivery_address;

            const itemsDiv = document.getElementById('order-items');
            itemsDiv.innerHTML = '<h4>Товары в заказе:</h4>';
            order.items.forEach(item => {
                itemsDiv.innerHTML += `<p>${item.product_name} - ${item.quantity} шт. по ${item.price_per_item} руб.</p>`;
            });

            modal.style.display = 'flex';

            form.onsubmit = async (e) => {
                e.preventDefault();
                if (!validateEditOrderForm(form)) return;

                const formData = new FormData(form);
                formData.append('action', 'edit_order');
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        closeModal('edit-order-modal');
                        loadData('orders', document.getElementById('order-search').value);
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при редактировании заказа');
                }
            };
        }

        async function deleteOrder(orderId) {
            if (!confirm('Вы уверены, что хотите удалить этот заказ?')) return;
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_order&order_id=${orderId}`,
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.status === 'success') {
                    loadData('orders', document.getElementById('order-search').value);
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при удалении заказа');
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
    </script>
</body>
</html>