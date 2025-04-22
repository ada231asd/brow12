<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акции</title>
    <link rel="stylesheet" href="/../brow12/css/style.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require '../Header/header.html'; ?>
    <div class="promotions-grid" id="promotionsContainer"></div>
    <?php require '../Foter/foter.html';?>
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
                                <img src="/brow12/${promo.image_url}" class="promotion-image" alt="${promo.title}" loading="lazy">
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