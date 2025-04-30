<?php
if (!checkRolePermission($userData['role_id'], 'get_reviews')) {
    echo '<h2>Доступ запрещен</h2>';
    return;
}
?>
<h2>Модерация отзывов</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Продукт</th>
            <th>Пользователь</th>
            <th>Рейтинг</th>
            <th>Комментарий</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody id="Reviews-table">
        <!-- Данные загружаются через AJAX -->
    </tbody>
</table>