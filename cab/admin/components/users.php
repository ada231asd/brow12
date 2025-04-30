<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_user', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_user', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_user', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Пользователи: Адепты цифрового культа</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addUserModal')">Добавить пользователя</button>
    <?php endif; ?>
    <input type="text" id="searchUsers" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="usersTable"></tbody>
    </table>

    <nav class="pagination" id="paginationUsers"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addUserModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addUserModal')">×</span>
        <h3>Новый пользователь</h3>
        <form id="addUserForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password_hash" required>
            </div>
            <div class="form-group">
                <label>Роль</label>
                <select name="role_id">
                    <?php
                    global $pdo;
                    $roles = $pdo->query("SELECT * FROM Role")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($roles as $role) {
                        echo "<option value='{$role['id']}'>{$role['Name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editUserModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editUserModal')">×</span>
        <h3>Редактировать пользователя</h3>
        <form id="editUserForm">
            <input type="hidden" name="user_id">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Пароль (оставьте пустым, если не меняете)</label>
                <input type="password" name="password_hash">
            </div>
            <div class="form-group">
                <label>Роль</label>
                <select name="role_id">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['Name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadUsers(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Users&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('usersTable');
                tbody.innerHTML = '';
                data.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.user_id}</td>
                        <td>${user.email}</td>
                        <td>${user.role_id}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editUser(${user.user_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteUser(${user.user_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationUsers');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadUsers(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchUsers').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadUsers(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        if (fields.password_hash) {
            fields.password_hash = '<?php echo password_hash("', fields.password_hash, '", PASSWORD_DEFAULT); ?>';
        }

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Users', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addUserModal');
                    loadUsers(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editUser = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=Users&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const user = data.data[0];
                    document.querySelector('#editUserForm [name="user_id"]').value = user.user_id;
                    document.querySelector('#editUserForm [name="email"]').value = user.email;
                    document.querySelector('#editUserForm [name="role_id"]').value = user.role_id;
                    document.querySelector('#editUserForm [name="password_hash"]').value = '';
                    openModal('editUserModal');
                }
            });
    };

    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.user_id;
        delete fields.user_id;
        if (fields.password_hash) {
            fields.password_hash = '<?php echo password_hash("', fields.password_hash, '", PASSWORD_DEFAULT); ?>';
        } else {
            delete fields.password_hash;
        }

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Users', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editUserModal');
                    loadUsers(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteUser = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Users', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadUsers(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadUsers();
});
</script>