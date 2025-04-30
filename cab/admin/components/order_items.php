<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_order_item', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_order_item', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_order_item', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Элементы заказа: Частицы цифрового потока</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addOrderItemModal')">Добавить элемент</button>
    <?php endif; ?>
    <input type="text" id="searchOrderItems" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Заказ</th>
                <th>Товар</th>
                <th>Количество</th>
                <th>Цена</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="orderItemsTable"></tbody>
    </table>

    <nav class="pagination" id="paginationOrderItems"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addOrderItemModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addOrderItemModal')">×</span>
        <h3>Новый элемент заказа</h3>
        <form id="addOrderItemForm">
            <div class="form-group">
                <label>Заказ</label>
                <select name="order_id">
                    <?php
                    global $pdo;
                    $orders = $pdo->query("SELECT * FROM Orders")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($orders as $order) {
                        echo "<option value='{$order['order_id']}'>{$order['order_code']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Товар</label>
                <select name="product_id">
                    <?php
                    $products = $pdo->query("SELECT * FROM Products")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($products as $product) {
                        echo "<option value='{$product['product_id']}'>{$product['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Количество</label>
                <input type="number" name="quantity" required>
            </div>
            <div class="form-group">
                <label>Цена</label>
                <input type="number" name="price" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editOrderItemModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editOrderItemModal')">×</span>
        <h3>Редактировать элемент заказа</h3>
        <form id="editOrderItemForm">
            <input type="hidden" name="order_item_id">
            <div class="form-group">
                <label>Заказ</label>
                <select name="order_id">
                    <?php foreach ($orders as $order): ?>
                        <option value="<?php echo $order['order_id']; ?>"><?php echo $order['order_code']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Товар</label>
                <select name="product_id">
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Количество</label>
                <input type="number" name="quantity" required>
            </div>
            <div class="form-group">
                <label>Цена</label>
                <input type="number" name="price" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadOrderItems(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Order_Items&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('orderItemsTable');
                tbody.innerHTML = '';
                data.data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.order_item_id}</td>
                        <td>${item.order_id}</td>
                        <td>${item.product_id}</td>
                        <td>${item.quantity}</td>
                        <td>${item.price}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editOrderItem(${item.order_item_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteOrderItem(${item.order_item_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationOrderItems');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadOrderItems(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchOrderItems').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadOrderItems(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addOrderItemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Order_Items', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addOrderItemModal');
                    loadOrderItems(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editOrderItem = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=Order_Items&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const item = data.data[0];
                    document.querySelector('#editOrderItemForm [name="order_item_id"]').value = item.order_item_id;
                    document.querySelector('#editOrderItemForm [name="order_id"]').value = item.order_id;
                    document.querySelector('#editOrderItemForm [name="product_id"]').value = item.product_id;
                    document.querySelector('#editOrderItemForm [name="quantity"]').value = item.quantity;
                    document.querySelector('#editOrderItemForm [name="price"]').value = item.price;
                    openModal('editOrderItemModal');
                }
            });
    };

    document.getElementById('editOrderItemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.order_item_id;
        delete fields.order_item_id;

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Order_Items', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editOrderItemModal');
                    loadOrderItems(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteOrderItem = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Order_Items', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadOrderItems(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadOrderItems();
});
</script>