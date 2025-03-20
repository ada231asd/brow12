<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Модерация отзывов</title>
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
    <h1>Модерация отзывов</h1>

    <div id="messageBox"></div>

    <select id="statusFilter" onchange="loadReviews()">
        <option value="">Все</option>
        <option value="На модерации">На модерации</option>
        <option value="Одобрен">Одобрен</option>
        <option value="Отклонен">Отклонен</option>
    </select>

    <input type="text" id="search" placeholder="Поиск отзывов..." oninput="searchReviews()">

    <table id="reviewsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Отзыв</th>
                <th>Рейтинг</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <!-- Данные загружаются динамически через AJAX -->
        </tbody>
    </table>
    <div id="noResults" class="no-results" style="display:none;">Отзывы не найдены</div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadReviews();
        });

        function loadReviews() {
            const status = document.getElementById('statusFilter').value;
            fetch('ajax/reviews.php?action=load&status=' + status)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#reviewsTable tbody').innerHTML = data;
                    document.getElementById('noResults').style.display = data.trim() === '' ? 'block' : 'none';
                });
        }

        function searchReviews() {
            const query = document.getElementById('search').value;
            const status = document.getElementById('statusFilter').value;
            fetch('ajax/reviews.php?action=search&query=' + query + '&status=' + status)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#reviewsTable tbody').innerHTML = data;
                    document.getElementById('noResults').style.display = data.trim() === '' ? 'block' : 'none';
                });
        }

        function approveReview(id) {
            fetch('ajax/reviews.php?action=approve&id=' + id)
                .then(() => loadReviews());
        }

        function rejectReview(id) {
            fetch('ajax/reviews.php?action=reject&id=' + id)
                .then(() => loadReviews());
        }

        function deleteReview(id) {
            if (confirm('Вы уверены, что хотите удалить этот отзыв?')) {
                fetch('ajax/reviews.php?action=delete&id=' + id)
                    .then(() => loadReviews());
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