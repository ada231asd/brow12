<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление обратной связью</title>
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
    <h1>Управление обратной связью</h1>

    <div id="messageBox"></div>

    <select id="statusFilter" onchange="loadFeedback()">
        <option value="">Все</option>
        <option value="Зарегистрирован">Зарегистрирован</option>
        <option value="В рассмотрении">В рассмотрении</option>
        <option value="Закрыт">Закрыт</option>
    </select>

    <input type="text" id="search" placeholder="Поиск сообщений..." oninput="searchFeedback()">

    <table id="feedbackTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Сообщение</th>
                <th>Контактные данные</th>
                <th>Статус</th>
                <th>Ответ</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <!-- Данные загружаются динамически через AJAX -->
        </tbody>
    </table>
    <div id="noResults" class="no-results" style="display:none;">Сообщений не найдено</div>

    <!-- Модальное окно ответа -->
    <div id="modal" class="modal">
        <h2 id="modalTitle">Ответ на сообщение</h2>
        <input type="hidden" id="feedbackId">
        <p id="feedbackMessage"></p>
        <p id="contactInfo"></p>
        <label>Ваш ответ:</label>
        <textarea id="responseContent" rows="5" cols="50"></textarea><br>
        <button onclick="sendResponse()">Отправить</button>
        <button onclick="closeModal()">Закрыть</button>
    </div>

    <div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadFeedback();
        });

        function loadFeedback() {
            const status = document.getElementById('statusFilter').value;
            fetch('ajax/feedback.php?action=load&status=' + status)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#feedbackTable tbody').innerHTML = data;
                    document.getElementById('noResults').style.display = data.trim() === '' ? 'block' : 'none';
                });
        }

        function searchFeedback() {
            const query = document.getElementById('search').value;
            const status = document.getElementById('statusFilter').value;
            fetch('ajax/feedback.php?action=search&query=' + query + '&status=' + status)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#feedbackTable tbody').innerHTML = data;
                    document.getElementById('noResults').style.display = data.trim() === '' ? 'block' : 'none';
                });
        }

        function openReplyModal(id, message, contact) {
            document.getElementById('modalTitle').innerText = 'Ответ на сообщение';
            document.getElementById('feedbackId').value = id;
            document.getElementById('feedbackMessage').innerText = message;
            document.getElementById('contactInfo').innerText = 'Контактная информация: ' + contact;
            document.getElementById('responseContent').value = '';
            document.getElementById('modal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
            
            // Меняем статус на "В рассмотрении"
            fetch('ajax/feedback.php?action=updateStatus&id=' + id + '&status=В рассмотрении');
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }

        function sendResponse() {
            const id = document.getElementById('feedbackId').value;
            const response = document.getElementById('responseContent').value;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('response', response);

            fetch('ajax/feedback.php?action=respond', {
                method: 'POST',
                body: formData
            }).then(() => {
                closeModal();
                loadFeedback();
            });
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