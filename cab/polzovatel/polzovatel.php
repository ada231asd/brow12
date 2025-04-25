<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: white;
        }

        .container {
            display: flex;
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .sidebar {
            flex: 0 0 200px;
            background: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
        }

        .general-info {
            margin-bottom: 20px;
        }

        .general-info img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .general-info p {
            font-size: 14px;
            margin: 5px 0;
        }

        nav button {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            background: none;
            border: none;
            color: white;
            text-align: left;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }

        nav button.active {
            background: #6b4de6;
        }

        nav button:hover {
            background: #3a3a3a;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
        }

        .section {
            display: none;
            background: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
        }

        .section.active {
            display: block;
        }

        h2 {
            margin-top: 0;
            font-size: 24px;
            color: #6b4de6;
        }

        .user-data p {
            font-size: 16px;
            margin: 10px 0;
        }

        .user-data button {
            background: #6b4de6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .user-data button:hover {
            background: #5a3ec5;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .orders-table th, .orders-table td {
            padding: 10px;
            border: 1px solid #444;
            text-align: left;
            font-size: 14px;
        }

        .orders-table th {
            background: #3a3a3a;
        }

        .orders-table td {
            background: #2a2a2a;
        }

        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: #3a3a3a;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }

        .product-card h3 {
            font-size: 16px;
            margin: 10px 0 5px;
        }

        .product-card p {
            font-size: 14px;
            color: #ccc;
        }

        #passwordForm {
            display: grid;
            gap: 10px;
            max-width: 300px;
        }

        #passwordForm input {
            padding: 10px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #3a3a3a;
            color: white;
        }

        #passwordForm input:invalid {
            border-color: #ff6b6b;
        }

        #passwordForm input:valid {
            border-color: #6b4de6;
        }

        #passwordForm button {
            background: #6b4de6;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        #passwordForm button:hover {
            background: #5a3ec5;
        }

        #editModal {
            background: #2a2a2a;
            border: none;
            border-radius: 10px;
            padding: 20px;
            color: white;
        }

        #editForm {
            display: grid;
            gap: 10px;
        }

        #editForm label {
            display: flex;
            flex-direction: column;
            font-size: 14px;
        }

        #editForm input, #editForm textarea {
            padding: 10px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #3a3a3a;
            color: white;
            margin-top: 5px;
        }

        #editForm button {
            background: #6b4de6;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        #editForm button:hover {
            background: #5a3ec5;
        }

        #editForm button[type="button"] {
            background: #ff6b6b;
        }

        #editForm button[type="button"]:hover {
            background: #ff5252;
        }

        .logout-btn {
            background-color: #ff6b6b;
            color: white;
        }

        .logout-btn:hover {
            background-color: #ff5252;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Сайдбар с навигацией -->
        <div class="sidebar">
            <div class="general-info">
                <img id="userAvatar" src="ajax/uploads/default_avatar.jpg" alt="Аватар">
                <p>Зарегистрирован: <span id="regDate"></span></p>
                <p>Заказов: <span id="ordersCount"></span></p>
            </div>
            <nav>
                <button id="personalBtn" onclick="showSection('personal')">Личные данные</button>
                <button id="ordersBtn" onclick="showSection('orders')">История заказов</button>
                <button id="favoritesBtn" onclick="showSection('favorites')">Избранное</button>
                <button id="passwordBtn" onclick="showSection('password')">Сменить пароль</button>
                <button id="logoutBtn" class="logout-btn">Выйти</button>
            </nav>
        </div>

        <!-- Основной контент -->
        <div class="content">
            <!-- Личные данные -->
            <section id="personal" class="section active">
                <h2>Личные данные</h2>
                <div class="user-data">
                    <p>Имя: <span id="userName"></span></p>
                    <p>Email: <span id="userEmail"></span></p>
                    <p>Телефон: <span id="userPhone"></span></p>
                    <p>Адрес: <span id="userAddress"></span></p>
                    <button onclick="openEditModal()">Редактировать</button>
                </div>
            </section>

            <!-- История заказов -->
            <section id="orders" class="section">
                <h2>История заказов</h2>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Дата</th>
                            <th>Товаров</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody id="ordersList"></tbody>
                </table>
            </section>

            <!-- Избранное -->
            <section id="favorites" class="section">
                <h2>Избранное</h2>
                <div class="favorites-grid" id="favoritesGrid"></div>
            </section>

            <!-- Смена пароля -->
            <section id="password" class="section">
                <h2>Смена пароля</h2>
                <form id="passwordForm">
                    <input type="password" name="old_password" placeholder="Старый пароль" required>
                    <input type="password" name="new_password" placeholder="Новый пароль" required minlength="6">
                    <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
                    <button type="submit">Сменить пароль</button>
                </form>
                <p id="passwordError" class="error-message"></p>
            </section>
        </div>

        <!-- Модальное окно редактирования -->
        <dialog id="editModal">
            <form id="editForm">
                <label>Имя: <input type="text" name="name" required></label>
                <label>Email: <input type="email" name="email" required></label>
                <label>Телефон: <input type="tel" name="phone"></label>
                <label>Адрес: <textarea name="address"></textarea></label>
                <button type="button" onclick="closeEditModal()">Отмена</button>
                <button type="submit">Сохранить</button>
            </form>
        </dialog>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            await loadUserData();
            showSection('personal'); // Ensure the personal section is active by default
        });

        async function loadUserData() {
            try {
                const response = await fetch('/../brow12/api/get_full_user.php');
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Общие данные
                    document.getElementById('userAvatar').src = data.data.user.photo || 'ajax/uploads/default_avatar.jpg';
                    document.getElementById('regDate').textContent = new Date(data.data.user.created_at).toLocaleDateString();
                    document.getElementById('ordersCount').textContent = data.data.orders.length;
                    
                    // Личные данные
                    document.getElementById('userName').textContent = data.data.user.name;
                    document.getElementById('userEmail').textContent = data.data.user.email;
                    document.getElementById('userPhone').textContent = data.data.user.phone || 'Не указан';
                    document.getElementById('userAddress').textContent = data.data.user.delivery_address || 'Не указан';

                    // Populate edit form with current data
                    document.querySelector('#editForm [name="name"]').value = data.data.user.name;
                    document.querySelector('#editForm [name="email"]').value = data.data.user.email;
                    document.querySelector('#editForm [name="phone"]').value = data.data.user.phone || '';
                    document.querySelector('#editForm [name="address"]').value = data.data.user.delivery_address || '';
                    
                    // Заказы
                    renderOrders(data.data.orders);
                    
                    // Избранное
                    renderFavorites(data.data.favorites);
                } else {
                    alert('Ошибка загрузки данных: ' + data.message);
                }
            } catch (error) {
                console.error('Ошибка загрузки данных:', error);
                alert('Произошла ошибка при загрузке данных. Попробуйте позже.');
            }
        }

        function renderOrders(orders) {
            const tbody = document.getElementById('ordersList');
            tbody.innerHTML = orders.map(order => `
                <tr>
                    <td>${order.order_id}</td>
                    <td>${new Date(order.created_at).toLocaleDateString()}</td>
                    <td>${order.items.reduce((sum, item) => sum + item.quantity, 0)}</td>
                    <td>${order.total_price} ₽</td>
                    <td>${order.delivery_status || order.order_status}</td>
                </tr>
            `).join('');
        }

        function renderFavorites(favorites) {
            const grid = document.getElementById('favoritesGrid');
            grid.innerHTML = favorites.map(item => `
                <div class="product-card">
                    <img src="${item.image_url || 'ajax/uploads/default_product.jpg'}" alt="${item.name}">
                    <h3>${item.name}</h3>
                    <p>${item.price} ₽</p>
                </div>
            `).join('');
        }

        // Модальное окно
        function openEditModal() {
            const modal = document.getElementById('editModal');
            modal.showModal();
        }

        function closeEditModal() {
            document.getElementById('editModal').close();
        }

        // Отправка формы редактирования
        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/../brow12/api/update_user.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    closeEditModal();
                    await loadUserData(); // Обновляем данные
                } else {
                    alert('Ошибка обновления: ' + result.message);
                }
            } catch (error) {
                console.error('Ошибка обновления:', error);
                alert('Произошла ошибка при обновлении данных. Попробуйте позже.');
            }
        });

        // Валидация пароля
        document.getElementById('passwordForm').addEventListener('input', (e) => {
            const newPass = document.querySelector('input[name="new_password"]');
            const confirmPass = document.querySelector('input[name="confirm_password"]');
            const errorElement = document.getElementById('passwordError');
            
            if (newPass.value !== confirmPass.value) {
                confirmPass.setCustomValidity('Пароли не совпадают');
                errorElement.textContent = 'Пароли не совпадают';
            } else {
                confirmPass.setCustomValidity('');
                errorElement.textContent = '';
            }
        });

        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const errorElement = document.getElementById('passwordError');
            
            try {
                const response = await fetch('/../brow12/api/change_password.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert('Пароль успешно изменен!');
                    e.target.reset();
                    errorElement.textContent = '';
                } else {
                    errorElement.textContent = result.message;
                }
            } catch (error) {
                console.error('Ошибка:', error);
                errorElement.textContent = 'Произошла ошибка. Попробуйте позже.';
            }
        });

        function showSection(sectionId) {
            // Hide all sections and remove active class from buttons
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelectorAll('.sidebar nav button').forEach(button => {
                button.classList.remove('active');
            });

            // Show selected section and mark button as active
            document.getElementById(sectionId).classList.add('active');
            document.getElementById(`${sectionId}Btn`).classList.add('active');
        }

        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                const response = await fetch('/../brow12/api/logout.php', {
                    method: 'POST',
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    window.location.href = '/../brow12/index.php';
                } else {
                    alert('Ошибка при выходе. Попробуйте снова.');
                }
            } catch (error) {
                console.error('Ошибка при выходе:', error);
                alert('Произошла ошибка при выходе. Попробуйте позже.');
            }
        });
    </script>
</body>
</html> 