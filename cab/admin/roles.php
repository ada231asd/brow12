<?php
if (!checkRolePermission($userData['role_id'], 'edit_role')) {
    echo '<h2>Доступ запрещен</h2>';
    return;
}

$tablesStmt = $pdo->query("SHOW TABLES");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
$actions = ['get', 'add', 'edit', 'delete'];
$allPermissions = ['edit_role'];
foreach ($tables as $table) {
    foreach ($actions as $action) {
        $allPermissions[] = "$action_$table";
    }
}

// Перевод прав
$permissionTranslations = [
    'edit_role' => 'Редактирование ролей',
    'get_Products' => 'Просмотр товаров',
    'add_Products' => 'Добавление товаров',
    'edit_Products' => 'Редактирование товаров',
    'delete_Products' => 'Удаление товаров',
    'get_Orders' => 'Просмотр заказов',
    'add_Orders' => 'Добавление заказов',
    'edit_Orders' => 'Редактирование заказов',
    'delete_Orders' => 'Удаление заказов',
    'get_Users' => 'Просмотр пользователей',
    'add_Users' => 'Добавление пользователей',
    'edit_Users' => 'Редактирование пользователей',
    'delete_Users' => 'Удаление пользователей',
    'get_Reviews' => 'Просмотр отзывов',
    'add_Reviews' => 'Добавление отзывов',
    'edit_Reviews' => 'Редактирование отзывов',
    'delete_Reviews' => 'Удаление отзывов',
    'get_Categories' => 'Просмотр категорий',
    'add_Categories' => 'Добавление категорий',
    'edit_Categories' => 'Редактирование категорий',
    'delete_Categories' => 'Удаление категорий',
    'get_News' => 'Просмотр новостей',
    'add_News' => 'Добавление новостей',
    'edit_News' => 'Редактирование новостей',
    'delete_News' => 'Удаление новостей',
    'get_Promotions' => 'Просмотр акций',
    'add_Promotions' => 'Добавление акций',
    'edit_Promotions' => 'Редактирование акций',
    'delete_Promotions' => 'Удаление акций',
    'get_Cart' => 'Просмотр корзины',
    'add_Cart' => 'Добавление корзины',
    'edit_Cart' => 'Редактирование корзины',
    'delete_Cart' => 'Удаление корзины'
    // Добавь остальные права по аналогии, если нужно
];
?>
<h2>Управление ролями</h2>
<button onclick="openModal('create', 'Role')">Добавить роль</button>
<table>
    <thead>
        <tr>
            <th>Название</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody id="Role-table">
        <!-- Данные загружаются через AJAX -->
    </tbody>
</table>

<div id="permissions-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closePermissionsModal()">×</span>
        <h2 id="permissions-modal-title">Управление правами</h2>
        <form id="permissions-form">
            <?php foreach ($allPermissions as $perm): ?>
                <div>
                    <input type="checkbox" name="permissions[]" value="<?php echo $perm; ?>">
                    <label><?php echo $permissionTranslations[$perm] ?? $perm; ?></label>
                </div>
            <?php endforeach; ?>
            <button type="button" onclick="savePermissions()">Сохранить</button>
        </form>
    </div>
</div>

<style>
    #permissions-modal .modal-content {
        max-height: 80vh;
        overflow-y: auto;
    }
    #permissions-form div {
        margin: 5px 0;
    }
    #permissions-form input[type="checkbox"] {
        margin-right: 5px;
    }
</style>