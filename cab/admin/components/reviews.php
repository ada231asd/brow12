<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!checkAuth()) {
    header('Location: /cab/admin/login');
    exit;
}

$permissions = getUserPermissions($_COOKIE['user_id']);
$canAdd = in_array('add_review', $permissions) || in_array('admin_full_access', $permissions);
$canEdit = in_array('edit_review', $permissions) || in_array('admin_full_access', $permissions);
$canDelete = in_array('delete_review', $permissions) || in_array('admin_full_access', $permissions);
?>

<section class="section">
    <h2>Отзывы: Голоса цифровых душ</h2>
    <?php if ($canAdd): ?>
        <button class="btn" onclick="openModal('addReviewModal')">Добавить отзыв</button>
    <?php endif; ?>
    <input type="text" id="searchReviews" placeholder="Поиск...">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Товар</th>
                <th>Рейтинг</th>
                <th>Комментарий</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="reviewsTable"></tbody>
    </table>

    <nav class="pagination" id="paginationReviews"></nav>
</section>

<!-- Модальное окно для добавления -->
<div class="modal" id="addReviewModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addReviewModal')">×</span>
        <h3>Новый отзыв</h3>
        <form id="addReviewForm">
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
                <label>Рейтинг (1-5)</label>
                <input type="number" name="rating" min="1" max="5" required>
            </div>
            <div class="form-group">
                <label>Комментарий</label>
                <textarea name="comment" required></textarea>
            </div>
            <div class="form-group">
                <label>Дата</label>
                <input type="date" name="review_date" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования -->
<div class="modal" id="editReviewModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editReviewModal')">×</span>
        <h3>Редактировать отзыв</h3>
        <form id="editReviewForm">
            <input type="hidden" name="review_id">
            <div class="form-group">
                <label>Пользователь</label>
                <select name="user_id">
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>"><?php echo $user['email']; ?></option>
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
                <label>Рейтинг (1-5)</label>
                <input type="number" name="rating" min="1" max="5" required>
            </div>
            <div class="form-group">
                <label>Комментарий</label>
                <textarea name="comment" required></textarea>
            </div>
            <div class="form-group">
                <label>Дата</label>
                <input type="date" name="review_date" required>
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTimeout;

    function loadReviews(page = 1, search = '') {
        fetch(`/cab/admin/api/get_data.php?table=Reviews&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('reviewsTable');
                tbody.innerHTML = '';
                data.data.forEach(review => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${review.review_id}</td>
                        <td>${review.user_id}</td>
                        <td>${review.product_id}</td>
                        <td>${review.rating}</td>
                        <td>${review.comment.substring(0, 50)}...</td>
                        <td>${review.review_date}</td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="btn btn-edit" onclick="editReview(${review.review_id})">Редактировать</button>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <button class="btn btn-delete" onclick="deleteReview(${review.review_id})">Удалить</button>
                            <?php endif; ?>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const pagination = document.getElementById('paginationReviews');
                pagination.innerHTML = '';
                for (let i = 1; i <= data.pages; i++) {
                    const li = document.createElement('li');
                    li.className = i === page ? 'active' : '';
                    li.innerHTML = `<a href="#" onclick="loadReviews(${i}, '${search}'); return false;">${i}</a>`;
                    pagination.appendChild(li);
                }
            });
    }

    document.getElementById('searchReviews').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadReviews(currentPage, this.value);
        }, 300);
    });

    document.getElementById('addReviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);

        fetch('/cab/admin/api/create_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Reviews', fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('addReviewModal');
                    loadReviews(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.editReview = function(id) {
        fetch(`/cab/admin/api/get_data.php?table=Reviews&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data[0]) {
                    const review = data.data[0];
                    document.querySelector('#editReviewForm [name="review_id"]').value = review.review_id;
                    document.querySelector('#editReviewForm [name="user_id"]').value = review.user_id;
                    document.querySelector('#editReviewForm [name="product_id"]').value = review.product_id;
                    document.querySelector('#editReviewForm [name="rating"]').value = review.rating;
                    document.querySelector('#editReviewForm [name="comment"]').value = review.comment;
                    document.querySelector('#editReviewForm [name="review_date"]').value = review.review_date;
                    openModal('editReviewModal');
                }
            });
    };

    document.getElementById('editReviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fields = {};
        formData.forEach((value, key) => fields[key] = value);
        const id = fields.review_id;
        delete fields.review_id;

        fetch('/cab/admin/api/update_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table: 'Reviews', id, fields })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editReviewModal');
                    loadReviews(currentPage);
                } else {
                    alert(data.error);
                }
            });
    });

    window.deleteReview = function(id) {
        if (confirm('Вы уверены? Хаос не прощает ошибок.')) {
            fetch('/cab/admin/api/delete_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ table: 'Reviews', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadReviews(currentPage);
                    } else {
                        alert(data.error);
                    }
                });
        }
    };

    loadReviews();
});
</script>