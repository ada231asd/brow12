<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_role_permission', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_role_permission', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_role_permission', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Права ролей: Законы хаоса</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addRolePermissionModal')">Добавить право</button>
    <?php endif; ?>
    <input type="text" id="searchRolePermissions" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Роль</th>
                <th>Право</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="rolePermissionsTable"></tbody>
    </table>

    <nav class="pagination" id="paginationRolePermissions"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addRolePermissionModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addRolePermissionModal')">×</span>
        <h3>Новое право</h3>
        <form id="addRolePermissionForm">
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
            <div class="form-group">
                <label>Право</label>
                <input type="text" name="permission" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editRolePermissionModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editRolePermissionModal')">×</span>
        <h3>Редактировать право</h3>
        <form id="editRolePermissionForm">
            <input type="hidden" name="role_permission_id">
            <div class="form-group">
                <label>Роль</label>
                <select name="role_id">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['Name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Право</label>
                <input type="text" name="permission" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadRolePermissions(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Role_Permissions&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('rolePermissionsTable');
                tbody.innerHTML = '';
                data.data.forEach(permission => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${permission.role_permission_id}</td>
                        <td>${permission.role_id}</td>
                        <td>${permission.permission}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editRolePermission(${permission.role_permission_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteRolePermission(${permission.role_permission_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationRolePermissions');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadRolePermissions(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.get