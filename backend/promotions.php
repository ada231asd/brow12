<?php
require_once 'ajax/db.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление акциями</title>
    <style>
        body {
            background: #1a1a1a;
            color: #e0e0e0;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .promotion-card {
            background: #2c2c2c;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #bb86fc;
        }

        .promotion-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .status-active {
            background: #4CAF50;
            color: white;
        }

        .status-expired {
            background: #f44336;
            color: white;
        }

        button {
            background: #bb86fc;
            color: #1a1a1a;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
            transition: 0.3s;
        }

        button:hover {
            background: #9a67ea;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
        }

        .modal-content {
            background: #2c2c2c;
            padding: 20px;
            width: 500px;
            margin: 50px auto;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Управление акциями</h1>
        <button onclick="openAddPromotionModal()">+ Новая акция</button>
        <div id="promotions-list"></div>
    </div>

    <!-- Модальное окно добавления акции -->
    <div id="add-promotion-modal" class="modal">
        <div class="modal-content">
            <h2>Новая акция</h2>
            <form id="add-promotion-form">
                <label>Название:</label>
                <input type="text" name="title" required>
                
                <label>Описание:</label>
                <textarea name="description" required></textarea>
                
                <label>Категория:</label>
                <select name="category_id" required>
                    <option value="">Выберите категорию</option>
                </select>
                
                <label>Дата начала:</label>
                <input type="date" name="start_date" required>
                
                <label>Дата окончания:</label>
                <input type="date" name="end_date" required>
                
                <button type="submit">Создать</button>
                <button type="button" onclick="closeModal()">Отмена</button>
            </form>
        </div>
    </div>

    <script>
        // Загрузка данных при открытии страницы
        document.addEventListener('DOMContentLoaded', () => {
            loadPromotions();
            loadCategories();
        });

        // Загрузка списка акций
        async function loadPromotions() {
            const response = await fetch('ajax/promotions.php?action=getPromotions');
            const promotions = await response.json();
            const list = document.getElementById('promotions-list');
            
            list.innerHTML = promotions.map(promo => `
                <div class="promotion-card">
                    <div>
                        <h3>${promo.title}</h3>
                        <p>${promo.description}</p>
                        <p>Категория: ${promo.category_name}</p>
                        <p>${promo.start_date} - ${promo.end_date}</p>
                    </div>
                    <div>
                        <span class="promotion-status ${promo.status === 'Активна' ? 'status-active' : 'status-expired'}">
                            ${promo.status}
                        </span>
                        <button onclick="togglePromotionStatus(${promo.promotion_id}, '${promo.status}')">
                            ${promo.status === 'Активна' ? 'Завершить' : 'Активировать'}
                        </button>
                        <button onclick="deletePromotion(${promo.promotion_id})">Удалить</button>
                    </div>
                </div>
            `).join('');
        }

        // Загрузка категорий для выпадающего списка
        async function loadCategories() {
            const response = await fetch('ajax/products.php?action=getCategories');
            const categories = await response.json();
            const select = document.querySelector('select[name="category_id"]');
            select.innerHTML = categories.map(cat => `
                <option value="${cat.category_id}">${cat.name}</option>
            `).join('');
        }

        // Открытие/закрытие модального окна
        function openAddPromotionModal() {
            document.getElementById('add-promotion-modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('add-promotion-modal').style.display = 'none';
        }

        // Добавление новой акции
        document.getElementById('add-promotion-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            const response = await fetch('ajax/promotions.php?action=addPromotion', {
                method: 'POST',
                body: new URLSearchParams(formData)
            });
            
            if(response.ok) {
                closeModal();
                loadPromotions();
            }
        });

        // Изменение статуса акции
        async function togglePromotionStatus(id, currentStatus) {
            const newStatus = currentStatus === 'Активна' ? 'Завершена' : 'Активна';
            
            await fetch(`ajax/promotions.php?action=updatePromotionStatus&id=${id}&status=${newStatus}`);
            loadPromotions();
        }

        // Удаление акции
        async function deletePromotion(id) {
            if(confirm('Вы уверены, что хотите удалить акцию?')) {
                await fetch(`ajax/promotions.php?action=deletePromotion&id=${id}`, {
                    method: 'DELETE'
                });
                loadPromotions();
            }
        }
    </script>
</body>
</html>