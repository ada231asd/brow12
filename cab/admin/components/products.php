<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_product', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_product', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_product', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Товары: Пульс цифрового хаоса</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addProductModal')">Добавить товар</button>
    <?php endif; ?>
    <input type="text" id="searchProducts" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Категория</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="productsTable"></tbody>
    </table>

    <nav class="pagination" id="pagination"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addProductModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addProductModal')">×</span>
        <h3>Новый товар</h3>
        <form id="addProductForm">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Категория</label>
                <select name="category_id">
                    <?php
                    global $pdo;
                    $categories = $pdo->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categories as $category) {
                        echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Цена</label>
                <input type="number" name="price" required>
            </div>
            <div class="form-group">
                <label>Количество</label>
                <input type="number" name="stock_quantity" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editProductModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editProductModal')">×</span>
        <h3>Редактировать товар</h3>
        <form id="editProductForm">
            <input type="hidden" name="product_id">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Категория</label>
                <select name="category_id">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Цена</label>
                <input type="number" name="price" required>
            </div>
            <div class="form-group">
                <label>Количество</label>
                <input type="number" name="stock_quantity" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadProducts(page = 1, search = '') {
        fetch(`/brow12/cab/admin/api/get_data.php?table=Products&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('productsTable');
                tbody.innerHTML = '';
                data.data.forEach(product => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${product.product_id}</td>
                        <td>${product.name}</td>
                        <td>${product.category_id}</td>
                        <td>${product.price}</td>
                        <td>${product.stock_quantity}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editProduct(${product.product_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteProduct(${product.product_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('pagination');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadProducts(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchProducts').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadProducts(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/brow12/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Products', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addProductModal');
                    loadProducts(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editProduct = function(id) {
        fetch(`/brow12/cab/admin/api/get_data.php?table=Products&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const product = data.data[0];
                    document.querySelector('#editProductForm [name="product_id"]').value = product.product_id;
                    document.querySelector('#editProductForm [name="name"]').value = product.name;
                    document.querySelector('#editProductForm [name="category_id"]').value = product.category_id;
                    document.querySelector('#editProductForm [name="price"]').value = product.price;
                    document.querySelector('#editProductForm [name="stock_quantity"]').value = product.stock_quantity;
                    openModal('editProductModal');
                }
            });
    };

    document.getElementById('editProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.product_id;
        delete fields.product_id;

        fetch('/brow12/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Products', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editProductModal');
                    loadProducts(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteProduct = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/brow12/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Products', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadProducts(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadProducts();
});
</script>