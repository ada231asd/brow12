<aside>
    <nav>
        <ul>
            <li><a href="?page=dashboard">Дашборд</a></li>
            <?php
            $tables = [
                'Products' => 'Товары',
                'Orders' => 'Заказы',
                'Users' => 'Пользователи',
                'Categories' => 'Категории',
                'Product_Characteristics' => 'Характеристики товаров',
                'News' => 'Новости',
                'Promotions' => 'Акции',
                'Cart' => 'Корзина',
                'Reviews' => 'Отзывы',
                'Role' => 'Роли', // Оставляем только "Роли"
                'User_Sessions' => 'Сессии пользователей',
                'Order_Items' => 'Элементы заказов',
                'Cart_Items' => 'Элементы корзины',
                'Comparison_Items' => 'Элементы сравнения',
                'Comparison_List' => 'Список сравнений',
                'Delivery_Status' => 'Статусы доставки',
                'Favorites' => 'Избранное',
                'Feedback' => 'Обратная связь',
                'Logs' => 'Логи',
                'Recommendations' => 'Рекомендации',
                'Stores' => 'Магазины',
                'Product_Characteristic_Values' => 'Значения характеристик товаров',
                'Admin_Logs' => 'Логи администраторов'
            ];
            foreach ($tables as $table => $displayName) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0 && checkRolePermission($userData['role_id'], "get_$table")): ?>
                    <li><a href="?page=tables&table=<?php echo $table; ?>"><?php echo $displayName; ?></a></li>
                <?php endif;
            }
            ?>
            <?php if (checkRolePermission($userData['role_id'], 'get_reviews')): ?>
                <li><a href="?page=reviews">Отзывы</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>