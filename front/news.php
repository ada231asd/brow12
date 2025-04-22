<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="style.css">
    <title>Новости</title>
</head>
<body>
<?php require '../Header/header.html'; ?>
    <div class="news-grid" id="newsGrid"></div>
    
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
    <div class="modal" id="newsModal" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
    <div class="blok_mod">
        <div class="modal_cont">
          <h2 id="modalTitle"></h2>  
          <p id="modalContent"></p>
          <button onclick="closeModal()">Закрыть</button>
        </div>
        <img id="modalImage" class="news-image" alt="Изображение новости" src="">
    </div>    
    </div>
    <?php require '../Foter/foter.html';?>
    <script>
   
        function loadNews() {
            fetch('../api/news.php') 
                .then(response => {
                    if (!response.ok) throw new Error('Ошибка сети');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        const grid = document.getElementById('newsGrid');
                        let html = '';
                        data.data.forEach(news => {
                            const formattedDate = new Date(news.created_at).toLocaleDateString('ru-RU');
                            html += `
                                <div class="news-card" onclick="showNews(${news.news_id})">
                                    <img src="/brow12/${news.image_url}" class="news-image" alt="${news.title}" loading="lazy">
                                    <div class="news-content">
                                        <div class="news-title">${news.title}</div>
                                        <div class="new-cont">${news.content}</div> 
                                        <div class="down-new-cont">
                                            <div class="new-dop">Подробнее ></div>
                                            <div class="new-date">${formattedDate}</div>
                                        </div>    
                                    </div>
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
                        document.getElementById('modalImage').src = `/brow12/${data.data.image_url}`;
                        document.getElementById('modalContent').textContent = data.data.content;
                        modal.style.display = 'block';
                        overlay.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Не удалось загрузить новость');
                });
        }


        function closeModal() {
            document.getElementById('newsModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }


        document.addEventListener('DOMContentLoaded', loadNews);
    </script>
</body>
</html>