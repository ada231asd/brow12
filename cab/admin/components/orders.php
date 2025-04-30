<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_order', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_order', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_order', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Заказы: Потоки цифровой энергии</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addOrderModal')">Добавить заказ</button>
    <?php endif; ?>
    <input type="text" id="searchOrders" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Код заказа</th>
                <th>Пользователь</th>
                <th>Общая сумма</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="ordersTable"></tbody>
    </table>

    <nav class="pagination" id="paginationOrders"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addOrderModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addOrderModal')">×</span>
        <h3>Новый заказ</h3>
        <form id="addOrderForm">
            <div class="form-group">
                <label>Код заказа</label>
                <input type="text" name="order_code" required>
            </div>
            <div class="form-group">
                <label>Пользователь</label>
                <select name="user_id">
                    <?php
                    global $pdo;
                    $users = $pdo->query("SELECT * FROM Users")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($users as $user) {
                        echo "<option value='{$user['user_id']}'>{$user['email']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Общая сумма</label>
                <input type="number" name="total_price" required>
            </div>
            <div class="form-group">
                <label>Статус</label>
                <select name="status">
                    <option value="pending">Ожидает</option>
                    <option value="shipped">Отправлен</option>
                    <option value="delivered">Доставлен</option>
                    <option value="canceled">Отменен</option>
                </select>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editOrderModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editOrderModal')">×</span>
        <h3>Редактировать заказ</h3>
        <form id="editOrderForm">
            <input type="hidden" name="order_id">
            <div class="form-group">
                <label>Код заказа</label>
                <input type="text" name="order_code" required>
            </div>
            <div class="form-group">
                <label>Пользователь</label>
                <select name="user_id">
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>"><?php echo $user['email']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Общая сумма</label>
                <input type="number" name="total_price" required>
            </div>
            <div class="form-group">
                <label>Статус</label>
                <select name="status">
                    <option value="pending">Ожидает</option>
                    <option value="shipped">Отправлен</option>
                    <option value="delivered">Доставлен</option>
                    <option value="canceled">Отменен</option>
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

    function loadOrders(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Orders&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('ordersTable');
                tbody.innerHTML = '';
                data.data.forEach(order => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${order.order_id}</td>
                        <td>${order.order_code}</td>
                        <td>${order.user_id}</td>
                        <td>${order.total_price}</td>
                        <td>${order.status}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editOrder(${order.order_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteOrder(${order.order_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationOrders');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadOrders(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchOrders').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadOrders(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Orders', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addOrderModal');
                    loadOrders(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editOrder = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=Orders&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const order = data.data[0];
                    document.querySelector('#editOrderForm [name="order_id"]').value = order.order_id;
                    document.querySelector('#editOrderForm [name="order_code"]').value = order.order_code;
                    document.querySelector('#editOrderForm [name="user_id"]').value = order.user_id;
                    document.querySelector('#editOrderForm [name="total_price"]').value = order.total_price;
                    document.querySelector('#editOrderForm [name="status"]').value = order.status;
                    openModal('editOrderModal');
                }
            });
    };

    document.getElementById('editOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.order_id;
        delete fields.order_id;

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Orders', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editOrderModal');
                    loadOrders(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteOrder = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Orders', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadOrders(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadOrders();
});
</script>