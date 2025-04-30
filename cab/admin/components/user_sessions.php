<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canDelete = in_array('delete_user_session', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Сессии пользователей: Следы в цифровом эфире</h2>
    <input type="text" id="searchUserSessions" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Токен</th>
                <th>Дата истечения</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="userSessionsTable"></tbody>
    </table>

    <nav class="pagination" id="paginationUserSessions"></nav>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadUserSessions(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=User_Sessions&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('userSessionsTable');
                tbody.innerHTML = '';
                data.data.forEach(session => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${session.session_id}</td>
                        <td>${session.user_id}</td>
                        <td>${session.token.substring(0, 20)}...</td>
                        <td>${session.expires_at}</td>
                        <td>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteUserSession(${session.session_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationUserSessions');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadUserSessions(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchUserSessions').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadUserSessions(currentPage, this.value);
        }, 300);
    });

    window.deleteUserSession = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'User_Sessions', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadUserSessions(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadUserSessions();
});
</script>