<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_category', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_category', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_category', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Категории: Ячейки порядка в хаосе</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addCategoryModal')">Добавить категорию</button>
    <?php endif; ?>
    <input type="text" id="searchCategories" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="categoriesTable"></tbody>
    </table>

    <nav class="pagination" id="paginationCategories"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addCategoryModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addCategoryModal')">×</span>
        <h3>Новая категория</h3>
        <form id="addCategoryForm">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editCategoryModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editCategoryModal')">×</span>
        <h3>Редактировать категорию</h3>
        <form id="editCategoryForm">
            <input type="hidden" name="category_id">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadCategories(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Categories&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('categoriesTable');
                tbody.innerHTML = '';
                data.data.forEach(category => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${category.category_id}</td>
                        <td>${category.name}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editCategory(${category.category_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteCategory(${category.category_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationCategories');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadCategories(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchCategories').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadCategories(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Categories', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addCategoryModal');
                    loadCategories(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editCategory = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=Categories&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const category = data.data[0];
                    document.querySelector('#editCategoryForm [name="category_id"]').value = category.category_id;
                    document.querySelector('#editCategoryForm [name="name"]').value = category.name;
                    openModal('editCategoryModal');
                }
            });
    };

    document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.category_id;
        delete fields.category_id;

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Categories', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editCategoryModal');
                    loadCategories(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteCategory = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Categories', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCategories(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadCategories();
});
</script>