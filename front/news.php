<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="   .css">
    <title>Новости</title>
    <style>
        /* Стили с улучшениями */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .news-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            background: white;
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .news-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f5f5f5;
        }

        .news-title {
            padding: 15px;
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            z-index: 1000;
            box-shadow: 0 0 25px rgba(0,0,0,0.2);
        }

        .modal button {
            margin-top: 15px;
            padding: 8px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .news-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php require '../Header/header.html'; ?>
    <div class="news-grid" id="newsGrid"></div>
    
    <!-- Модальное окно с оверлеем -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
    <div class="modal" id="newsModal" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
        <h2 id="modalTitle"></h2>
        <img id="modalImage" class="news-image" alt="Изображение новости" src="">
        <p id="modalContent"></p>
        <p><small id="modalDate"></small></p>
        <button onclick="closeModal()">Закрыть</button>
    </div>
    <?php require '../Foter/foter.html';?>
    <script>
        // Загрузка списка новостей с обработкой ошибок
        function loadNews() {
            fetch('../api/news.php') // Абсолютный путь
                .then(response => {
                    if (!response.ok) throw new Error('Ошибка сети');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        const grid = document.getElementById('newsGrid');
                        let html = '';
                        data.data.forEach(news => {
                            html += `
                                <div class="news-card" onclick="showNews(${news.news_id})">
                                    <img src="${news.image_url}" 
                                         class="news-image" 
                                         alt="${news.title}" 
                                         loading="lazy">
                                    <div class="news-title">${news.title}</div>
                                </div>
                            `;
                        });
                        grid.innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Не удалось загрузить новости');
                });
        }

        // Открытие модального окна
        function showNews(id) {
            fetch(`../api/news.php?id=${id}`)
                .then(response => {
                    if (!response.ok) throw new Error('Ошибка сети');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        const modal = document.getElementById('newsModal');
                        const overlay = document.getElementById('modalOverlay');
                        document.getElementById('modalTitle').textContent = data.data.title;
                        document.getElementById('modalImage').src = data.data.image_url;
                        document.getElementById('modalContent').textContent = data.data.content;
                        document.getElementById('modalDate').textContent = 
                            new Date(data.data.created_at).toLocaleDateString('ru-RU');
                        modal.style.display = 'block';
                        overlay.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Не удалось загрузить новость');
                });
        }

        // Закрытие модального окна
        function closeModal() {
            document.getElementById('newsModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }

        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', loadNews);
    </script>
</body>
</html>