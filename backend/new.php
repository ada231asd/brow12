<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление новостями</title>
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
        }
    </style>
</head>
<body>
    <h1>Управление новостями</h1>

    <div id="messageBox"></div>

    <input type="text" id="search" placeholder="Поиск новостей..." oninput="searchNews()">
    <button onclick="openAddModal()">Добавить новость</button>

    <table id="newsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Содержание</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <!-- Данные загружаются динамически через AJAX -->
        </tbody>
    </table>
    <div id="noResults" class="no-results" style="display:none;">Новости не найдены</div>

    <!-- Модальное окно добавления/редактирования -->
    <div id="modal" class="modal">
        <h2 id="modalTitle">Добавить новость</h2>
        <input type="hidden" id="newsId">
        <label>Заголовок:</label>
        <input type="text" id="newsTitle"><br>
        <label>Содержание:</label>
        <textarea id="newsContent" rows="5" cols="50"></textarea><br>
        <button onclick="saveNews()">Сохранить</button>
        <button onclick="closeModal()">Закрыть</button>
    </div>

    <div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadNews();
        });

        function loadNews() {
            fetch('ajax/news.php?action=load')
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#newsTable tbody').innerHTML = data;
                    document.getElementById('noResults').style.display = data.trim() === '' ? 'block' : 'none';
                });
        }

        function searchNews() {
            const query = document.getElementById('search').value;
            fetch('ajax/news.php?action=search&query=' + query)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#newsTable tbody').innerHTML = data;
                    document.getElementById('noResults').style.display = data.trim() === '' ? 'block' : 'none';
                });
        }

        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Добавить новость';
            document.getElementById('newsId').value = '';
            document.getElementById('newsTitle').value = '';
            document.getElementById('newsContent').value = '';
            document.getElementById('modal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function openEditModal(id, title, content) {
            document.getElementById('modalTitle').innerText = 'Редактировать новость';
            document.getElementById('newsId').value = id;
            document.getElementById('newsTitle').value = title;
            document.getElementById('newsContent').value = content;
            document.getElementById('modal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }

        function saveNews() {
            const id = document.getElementById('newsId').value;
            const title = document.getElementById('newsTitle').value;
            const content = document.getElementById('newsContent').value;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('title', title);
            formData.append('content', content);

            fetch('ajax/news.php?action=save', {
                method: 'POST',
                body: formData
            }).then(() => {
                closeModal();
                loadNews();
            });
        }

        function deleteNews(id) {
            if (confirm('Вы уверены, что хотите удалить новость?')) {
                fetch('ajax/news.php?action=delete&id=' + id)
                    .then(() => loadNews());
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