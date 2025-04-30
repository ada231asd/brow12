<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_characteristic', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_characteristic', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_characteristic', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Характеристики: Код цифровой сущности</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addCharacteristicModal')">Добавить характеристику</button>
    <?php endif; ?>
    <input type="text" id="searchCharacteristics" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Товар</th>
                <th>Название</th>
                <th>Значение</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="characteristicsTable"></tbody>
    </table>

    <nav class="pagination" id="paginationCharacteristics"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addCharacteristicModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addCharacteristicModal')">×</span>
        <h3>Новая характеристика</h3>
        <form id="addCharacteristicForm">
            <div class="form-group">
                <label>Товар</label>
                <select name="product_id">
                    <?php
                    global $pdo;
                    $products = $pdo->query("SELECT * FROM Products")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($products as $product) {
                        echo "<option value='{$product['product_id']}'>{$product['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Значение</label>
                <input type="text" name="value" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editCharacteristicModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editCharacteristicModal')">×</span>
        <h3>Редактировать характеристику</h3>
        <form id="editCharacteristicForm">
            <input type="hidden" name="characteristic_id">
            <div class="form-group">
                <label>Товар</label>
                <select name="product_id">
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Значение</label>
                <input type="text" name="value" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadCharacteristics(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Product_Characteristics&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('characteristicsTable');
                tbody.innerHTML = '';
                data.data.forEach(characteristic => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${characteristic.characteristic_id}</td>
                        <td>${characteristic.product_id}</td>
                        <td>${characteristic.name}</td>
                        <td>${characteristic.value}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editCharacteristic(${characteristic.characteristic_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteCharacteristic(${characteristic.characteristic_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationCharacteristics');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadCharacteristics(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchCharacteristics').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadCharacteristics(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addCharacteristicForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Product_Characteristics', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addCharacteristicModal');
                    loadCharacteristics(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editCharacteristic = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=Product_Characteristics&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const characteristic = data.data[0];
                    document.querySelector('#editCharacteristicForm [name="characteristic_id"]').value = characteristic.characteristic_id;
                    document.querySelector('#editCharacteristicForm [name="product_id"]').value = characteristic.product_id;
                    document.querySelector('#editCharacteristicForm [name="name"]').value = characteristic.name;
                    document.querySelector('#editCharacteristicForm [name="value"]').value = characteristic.value;
                    openModal('editCharacteristicModal');
                }
            });
    };

    document.getElementById('editCharacteristicForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.characteristic_id;
        delete fields.characteristic_id;

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Product_Characteristics', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editCharacteristicModal');
                    loadCharacteristics(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteCharacteristic = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Product_Characteristics', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCharacteristics(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadCharacteristics();
});
</script>