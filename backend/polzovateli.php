<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            z-index: 1;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
        }
        .no-results {
            color: red;
            font-weight: bold;
        }
        #messageBox {
            color: red;
            font-weight: bold;
            display: none;
            margin-bottom: 10px;
            z-index: 2;
        }
    </style>
</head>
<body>
    <h1>Управление пользователями</h1>

    <div id="messageBox"></div>

    <input type="text" id="search" placeholder="Поиск пользователей..." oninput="searchUsers()">
    <button onclick="openAddModal()">Добавить пользователя</button>

    <table id="usersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <!-- Данные загружаются динамически через AJAX -->
        </tbody>
    </table>
    <div id="noResults" class="no-results" style="display:none;">Такой пользователь не найден</div>

    <!-- Модальное окно добавления/редактирования -->
    <div id="modal" class="modal">
        <h2 id="modalTitle">Добавить пользователя</h2>
        <input type="hidden" id="userId">
        <label>Имя:</label>
        <input type="text" id="userName"><br>
        <label>Email:</label>
        <input type="email" id="userEmail"><br>
        <label>Пароль:</label>
        <input type="password" id="userPassword" placeholder="Оставьте пустым для пароля по умолчанию"><br>
        <label>Роль:</label>
        <select id="userRole" onchange="checkAdminRole()">
            <option value="Пользователь">Пользователь</option>
            <option value="Курьер">Курьер</option>
            <option value="Администратор">Администратор</option>
        </select><br><br>
        <span id="adminWarning" style="color: red; display: none;">Назначение роли администратора запрещено. Обратитесь к руководству.</span>
        <button onclick="saveUser()">Сохранить</button>
        <button onclick="closeModal()">Закрыть</button>
    </div>

    <div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadUsers();
        });

        function loadUsers() {
            fetch('ajax/users.php?action=load')
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#usersTable tbody').innerHTML = data;
                    document.getElementById('noResults').style.display = data.trim() === '' ? 'block' : 'none';
                });
        }

        function searchUsers() {
            const query = document.getElementById('search').value;
            fetch('ajax/users.php?action=search&query=' + query)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#usersTable tbody').innerHTML = data;
                    document.getElementById('noResults').style.display = data.trim() === '' ? 'block' : 'none';
                });
        }

        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Добавить пользователя';
            document.getElementById('userId').value = '';
            document.getElementById('userName').value = '';
            document.getElementById('userEmail').value = '';
            document.getElementById('userPassword').value = '';
            document.getElementById('userRole').value = 'Пользователь';
            document.getElementById('adminWarning').style.display = 'none';
            document.getElementById('modal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function openEditModal(id, name, email, role) {
            document.getElementById('modalTitle').innerText = 'Редактировать пользователя';
            document.getElementById('userId').value = id;
            document.getElementById('userName').value = name;
            document.getElementById('userEmail').value = email;
            document.getElementById('userPassword').value = '';
            document.getElementById('userRole').value = role;
            document.getElementById('adminWarning').style.display = 'none';
            document.getElementById('modal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function checkAdminRole() {
            const role = document.getElementById('userRole').value;
            if (role === 'Администратор') {
                showMessage('Назначение роли администратора запрещено. Обратитесь к руководству.');
                document.getElementById('userRole').value = 'Пользователь';
            }
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }

        function saveUser() {
            const id = document.getElementById('userId').value;
            const name = document.getElementById('userName').value;
            const email = document.getElementById('userEmail').value;
            const password = document.getElementById('userPassword').value;
            const role = document.getElementById('userRole').value;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('name', name);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('role', role);

            fetch('ajax/users.php?action=save', {
                method: 'POST',
                body: formData
            }).then(() => {
                closeModal();
                loadUsers();
            });
        }

        function deleteUser(id) {
            if (confirm('Вы уверены, что хотите удалить пользователя?')) {
                fetch('ajax/users.php?action=delete&id=' + id)
                    .then(() => loadUsers());
            }
        }

        function showMessage(message) {
            const messageBox = document.getElementById('messageBox');
            messageBox.innerText = message;
            messageBox.style.display = 'block';

            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>