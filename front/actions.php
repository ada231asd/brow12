<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акции</title>
    <style>
        .promotions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px;
        }
        .promotion-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            transition: transform 0.3s;
        }
        .promotion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .promotion-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .promotion-content {
            padding: 15px;
        }
        .promotion-title {
            font-size: 1.2em;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .promotion-dates {
            font-size: 0.9em;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        .promotion-category {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="promotions-grid" id="promotionsContainer"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('../api/promotions.php')
                .then(response => {
                    if (!response.ok) throw new Error('Ошибка загрузки');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        const container = document.getElementById('promotionsContainer');
                        container.innerHTML = data.data.map(promo => `
                            <div class="promotion-card">
                                <img src="${promo.image_url}" 
                                     class="promotion-image" 
                                     alt="${promo.title}"
                                     loading="lazy">
                                <div class="promotion-content">
                                    <div class="promotion-title">${promo.title}</div>
                                    <div class="promotion-dates">
                                        ${new Date(promo.start_date).toLocaleDateString('ru-RU')} - 
                                        ${new Date(promo.end_date).toLocaleDateString('ru-RU')}
                                    </div>
                                    ${promo.category_name ? 
                                        `<div class="promotion-category">${promo.category_name}</div>` : ''}
                                    <p>${promo.description}</p>
                                </div>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Не удалось загрузить акции');
                });
        });
    </script>
</body>
</html>