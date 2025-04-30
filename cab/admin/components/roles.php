<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_role', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_role', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_role', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Роли: Иерархия хаоса</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addRoleModal')">Добавить роль</button>
    <?php endif; ?>
    <input type="text" id="searchRoles" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="rolesTable"></tbody>
    </table>

    <nav class="pagination" id="paginationRoles"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addRoleModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addRoleModal')">×</span>
        <h3>Новая роль</h3>
        <form id="addRoleForm">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="Name" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editRoleModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editRoleModal')">×</span>
        <h3>Редактировать роль</h3>
        <form id="editRoleForm">
            <input type="hidden" name="id">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="Name" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadRoles(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Role&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('rolesTable');
                tbody.innerHTML = '';
                data.data.forEach(role => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${role.id}</td>
                        <td>${role.Name}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editRole(${role.id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteRole(${role.id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationRoles');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadRoles(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchRoles').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadRoles(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addRoleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Role', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addRoleModal');
                    loadRoles(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editRole = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=Role&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const role = data.data[0];
                    document.querySelector('#editRoleForm [name="id"]').value = role.id;
                    document.querySelector('#editRoleForm [name="Name"]').value = role.Name;
                    openModal('editRoleModal');
                }
            });
    };

    document.getElementById('editRoleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.id;
        delete fields.id;

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Role', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editRoleModal');
                    loadRoles(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteRole = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Role', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadRoles(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadRoles();
});
</script>