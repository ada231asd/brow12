<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_news', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_news', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_news', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Новости: Эхо цифрового мира</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addNewsModal')">Добавить новость</button>
    <?php endif; ?>
    <input type="text" id="searchNews" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Содержание</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="newsTable"></tbody>
    </table>

    <nav class="pagination" id="paginationNews"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addNewsModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addNewsModal')">×</span>
        <h3>Новая новость</h3>
        <form id="addNewsForm">
            <div class="form-group">
                <label>Заголовок</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Содержание</label>
                <textarea name="content" required></textarea>
            </div>
            <div class="form-group">
                <label>Дата</label>
                <input type="date" name="published_date" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editNewsModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editNewsModal')">×</span>
        <h3>Редактировать новость</h3>
        <form id="editNewsForm">
            <input type="hidden" name="news_id">
            <div class="form-group">
                <label>Заголовок</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Содержание</label>
                <textarea name="content" required></textarea>
            </div>
            <div class="form-group">
                <label>Дата</label>
                <input type="date" name="published_date" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadNews(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=News&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('newsTable');
                tbody.innerHTML = '';
                data.data.forEach(news => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${news.news_id}</td>
                        <td>${news.title}</td>
                        <td>${news.content.substring(0, 50)}...</td>
                        <td>${news.published_date}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editNews(${news.news_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteNews(${news.news_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationNews');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadNews(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchNews').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadNews(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addNewsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'News', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addNewsModal');
                    loadNews(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editNews = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=News&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const news = data.data[0];
                    document.querySelector('#editNewsForm [name="news_id"]').value = news.news_id;
                    document.querySelector('#editNewsForm [name="title"]').value = news.title;
                    document.querySelector('#editNewsForm [name="content"]').value = news.content;
                    document.querySelector('#editNewsForm [name="published_date"]').value = news.published_date;
                    openModal('editNewsModal');
                }
            });
    };

    document.getElementById('editNewsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.news_id;
        delete fields.news_id;

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'News', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editNewsModal');
                    loadNews(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteNews = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'News', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadNews(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadNews();
});
</script>