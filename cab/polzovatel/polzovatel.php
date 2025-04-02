<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
  <style>
    .container {
    display: flex;
    gap: 30px;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.sidebar {
    flex: 0 0 250px;
    background: #f5f5f5;
    padding: 20px;
    border-radius: 10px;
}

.general-info img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin-bottom: 15px;
}

nav button {
    display: block;
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    cursor: pointer;
}

.content {
    flex-grow: 1;
}

.section {
    display: none;
}

.section.active {
    display: block;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th, .orders-table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.product-card {
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 5px;
}

#editModal form {
    display: grid;
    gap: 10px;
}

input[type="password"] {
    border: 2px solid #ddd;
    padding: 8px;
}

input:invalid {
    border-color: red;
}

input:valid {
    border-color: green;
}
.logout-btn {
    margin-top: 20px;
    background-color: #ff6b6b;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
}

.logout-btn:hover {
    background-color: #ff5252;
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
                <button onclick="showSection('personal')">Личные данные</button>
                <button onclick="showSection('orders')">История заказов</button>
                <button onclick="showSection('favorites')">Избранное</button>
                <button onclick="showSection('password')">Сменить пароль</button>
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
                    <input type="password" name="new_password" placeholder="Новый пароль" required>
                    <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
                    <button type="submit">Сменить пароль</button>
                </form>
            </section>
        </div>

        <!-- Модальное окно редактирования -->
        <dialog id="editModal">
            <form id="editForm">
                <label>Имя: <input type="text" name="name"></label>
                <label>Email: <input type="email" name="email"></label>
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
});

async function loadUserData() {
    try {
        const response = await fetch('/../brow12/api/get_full_user.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            // Общие данные
            document.getElementById('userAvatar').src = data.data.user.photo || 'default_avatar.jpg';
            document.getElementById('regDate').textContent = new Date(data.data.user.created_at).toLocaleDateString();
            document.getElementById('ordersCount').textContent = data.data.orders.length;
            
            // Личные данные
            document.getElementById('userName').textContent = data.data.user.name;
            document.getElementById('userEmail').textContent = data.data.user.email;
            document.getElementById('userPhone').textContent = data.data.user.phone || 'Не указан';
            document.getElementById('userAddress').textContent = data.data.user.delivery_address || 'Не указан';
            
            // Заказы
            renderOrders(data.data.orders);
            
            // Избранное
            renderFavorites(data.data.favorites);
        }
    } catch (error) {
        console.error('Ошибка загрузки данных:', error);
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
            <td>${order.delivery_status}</td>
        </tr>
    `).join('');
}

function renderFavorites(favorites) {
    const grid = document.getElementById('favoritesGrid');
    grid.innerHTML = favorites.map(item => `
        <div class="product-card">
            <img src="${item.image_url}" alt="${item.name}">
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
        
        if (response.ok) {
            closeEditModal();
            await loadUserData(); // Обновляем данные
        }
    } catch (error) {
        console.error('Ошибка обновления:', error);
    }
});

// Валидация пароля
document.getElementById('passwordForm').addEventListener('input', (e) => {
    const newPass = document.querySelector('input[name="new_password"]');
    const confirmPass = document.querySelector('input[name="confirm_password"]');
    
    if (newPass.value !== confirmPass.value) {
        confirmPass.setCustomValidity('Пароли не совпадают');
    } else {
        confirmPass.setCustomValidity('');
    }
});

document.getElementById('passwordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/../brow12/api/change_password.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            alert('Пароль успешно изменен!');
            e.target.reset();
        }
    } catch (error) {
        console.error('Ошибка:', error);
    }
});

function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');
}
document.getElementById('logoutBtn').addEventListener('click', async () => {
    try {
        const response = await fetch('/../brow12/api/logout.php', {
            method: 'POST',
            credentials: 'same-origin' // Важно для работы с куками
        });
        
        if (response.ok) {
            // Перенаправляем на главную страницу
            window.location.href = '/../brow12/index.php';
        }
    } catch (error) {
        console.error('Ошибка при выходе:', error);
    }
});
    </script>
</body>
</html>