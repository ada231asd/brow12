<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_promotion', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_promotion', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_promotion', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Акции: Искры цифровой выгоды</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addPromotionModal')">Добавить акцию</button>
    <?php endif; ?>
    <input type="text" id="searchPromotions" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Скидка (%)</th>
                <th>Дата начала</th>
                <th>Дата окончания</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="promotionsTable"></tbody>
    </table>

    <nav class="pagination" id="paginationPromotions"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addPromotionModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addPromotionModal')">×</span>
        <h3>Новая акция</h3>
        <form id="addPromotionForm">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Скидка (%)</label>
                <input type="number" name="discount" required>
            </div>
            <div class="form-group">
                <label>Дата начала</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="form-group">
                <label>Дата окончания</label>
                <input type="date" name="end_date" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editPromotionModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editPromotionModal')">×</span>
        <h3>Редактировать акцию</h3>
        <form id="editPromotionForm">
            <input type="hidden" name="promotion_id">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Скидка (%)</label>
                <input type="number" name="discount" required>
            </div>
            <div class="form-group">
                <label>Дата начала</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="form-group">
                <label>Дата окончания</label>
                <input type="date" name="end_date" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadPromotions(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Promotions&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('promotionsTable');
                tbody.innerHTML = '';
                data.data.forEach(promotion => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${promotion.promotion_id}</td>
                        <td>${promotion.name}</td>
                        <td>${promotion.discount}</td>
                        <td>${promotion.start_date}</td>
                        <td>${promotion.end_date}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editPromotion(${promotion.promotion_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deletePromotion(${promotion.promotion_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationPromotions');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadPromotions(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchPromotions').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadPromotions(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addPromotionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Promotions', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addPromotionModal');
                    loadPromotions(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editPromotion = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=Promotions&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const promotion = data.data[0];
                    document.querySelector('#editPromotionForm [name="promotion_id"]').value = promotion.promotion_id;
                    document.querySelector('#editPromotionForm [name="name"]').value = promotion.name;
                    document.querySelector('#editPromotionForm [name="discount"]').value = promotion.discount;
                    document.querySelector('#editPromotionForm [name="start_date"]').value = promotion.start_date;
                    document.querySelector('#editPromotionForm [name="end_date"]').value = promotion.end_date;
                    openModal('editPromotionModal');
                }
            });
    };

    document.getElementById('editPromotionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.promotion_id;
        delete fields.promotion_id;

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Promotions', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editPromotionModal');
                    loadPromotions(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deletePromotion = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Promotions', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadPromotions(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadPromotions();
});
</script>