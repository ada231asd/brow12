<?php
if (!isset($_GET['table']) || !checkRolePermission($userData['role_id'], 'get_' . $_GET['table'])) {
    echo '<h2>Доступ запрещен</h2>';
    return;
}
$table = $_GET['table'];

$fieldTranslations = [
    'product_name' => 'Товар',
    'user_email' => 'Пользователь',
    'rating' => 'Рейтинг',
    'comment' => 'Комментарий',
    'status' => 'Статус',
    'name' => 'Имя',
    'email' => 'Email',
    'role_name' => 'Роль',
    'order_code' => 'Код заказа',
    'order_status' => 'Статус заказа',
    'delivery_address' => 'Адрес доставки',
    'total_price' => 'Общая стоимость',
    'created_at' => 'Дата создания',
    'description' => 'Описание',
    'price' => 'Цена',
    'stock_quantity' => 'Количество на складе',
    'is_bestseller' => 'Бестселлер',
    'is_new' => 'Новинка',
    'discount' => 'Скидка',
    'title' => 'Заголовок',
    'content' => 'Содержание',
    'start_date' => 'Дата начала',
    'end_date' => 'Дата окончания',
    'quantity' => 'Количество',
    'action' => 'Действие',
    'table_name' => 'Таблица',
    'record_id' => 'ID записи',
    'action_type' => 'Тип действия',
    'admin_id' => 'Администратор',
    'action_description' => 'Описание действия',
    'message' => 'Сообщение',
    'category' => 'Категория',
    'value_type' => 'Тип значения',
    'value' => 'Значение',
    'price_per_item' => 'Цена за единицу',
    'updated_at' => 'Дата обновления',
    'address' => 'Адрес',
    'phone' => 'Телефон',
    'working_hours' => 'Часы работы',
    'expires_at' => 'Дата истечения',
    'image_url' => 'Фото'
];
?>
<h2>Управление: <?php echo $tables[$table] ?? $table; ?></h2>
<?php if ($table !== 'Users' && $table !== 'Product_Characteristic_Values'): ?>
    <button onclick="openModal('create', '<?php echo $table; ?>')">Добавить</button>
<?php endif; ?>
<?php if ($table === 'Products'): ?>
    <button onclick="openCharacteristicsModal()">Управление характеристиками</button>
<?php endif; ?>
<table>
    <thead>
        <tr>
            <?php
            $stmt = $pdo->query("SELECT * FROM $table LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                foreach (array_keys($row) as $key) {
                    if (in_array($key, ['_id', 'password_hash', 'token', 'user_id', 'product_id', 'order_id', 'review_id', 'cart_id', 'category_id', 'image_url', 'photo', 'phone', 'postal_code', 'preferred_payment_method', 'preferred_delivery_method'])) continue;
                    $displayName = $fieldTranslations[$key] ?? $key;
                    echo "<th>$displayName</th>";
                }
            }
            ?>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody id="<?php echo $table; ?>-table">
        <!-- Данные загружаются через AJAX -->
    </tbody>
</table>