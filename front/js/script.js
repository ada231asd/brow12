const API_URL = '/admin_api.php';
let currentSection = 'dashboard';
let currentPage = 1;
let perPage = 20;

document.addEventListener('DOMContentLoaded', () => {
    loadNotifications();
    loadSection(currentSection);
    document.querySelectorAll('.sidebar nav a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            currentSection = e.target.dataset.section;
            currentPage = 1;
            document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
            const sectionElement = document.getElementById(currentSection);
            if (sectionElement) {
                sectionElement.classList.add('active');
                document.getElementById('section-title').textContent = e.target.textContent;
                loadSection(currentSection);
            } else {
                console.error(`Section with ID ${currentSection} not found`);
            }
        });
    });

    document.getElementById('logout').addEventListener('click', () => {
        document.cookie = 'auth_token=; expires=Thu, 01 Jan 1970 00:00:00 GMT';
        window.location.href = '/login.html';
    });

    // Bind search inputs
    ['users', 'orders', 'products', 'categories', 'characteristics', 'news', 'promotions', 'carts', 
     'comparison_lists', 'delivery_statuses', 'favorites', 'feedback', 'logs', 'recommendations', 
     'reviews', 'stores', 'user_sessions', 'admin_logs'].forEach(section => {
        const searchInput = document.getElementById(`${section}-search`);
        if (searchInput) {
            searchInput.addEventListener('input', debounce(() => {
                currentPage = 1;
                loadSection(section);
            }, 300));
        }
    });

    // Bind filter selects
    ['products-category', 'characteristics-category', 'feedback-category', 'feedback-status'].forEach(filter => {
        const select = document.getElementById(`${filter}-filter`);
        if (select) {
            select.addEventListener('change', () => {
                currentPage = 1;
                loadSection(filter.split('-')[0]);
            });
        }
    });
});

async function loadSection(section) {
    try {
        if (section === 'dashboard') {
            await loadAnalytics();
        } else if (section === 'users') {
            await loadUsers();
        } else if (section === 'orders') {
            await loadOrders();
        } else if (section === 'products') {
            await loadProducts();
        } else if (section === 'categories') {
            await loadCategories();
        } else if (section === 'characteristics') {
            await loadCharacteristics();
        } else if (section === 'news') {
            await loadNews();
        } else if (section === 'promotions') {
            await loadPromotions();
        } else if (section === 'carts') {
            await loadCarts();
        } else if (section === 'comparison_lists') {
            await loadComparisonLists();
        } else if (section === 'delivery_statuses') {
            await loadDeliveryStatuses();
        } else if (section === 'favorites') {
            await loadFavorites();
        } else if (section === 'feedback') {
            await loadFeedback();
        } else if (section === 'logs') {
            await loadLogs();
        } else if (section === 'recommendations') {
            await loadRecommendations();
        } else if (section === 'reviews') {
            await loadReviews();
        } else if (section === 'stores') {
            await loadStores();
        } else if (section === 'user_sessions') {
            await loadUserSessions();
        } else if (section === 'admin_logs') {
            await loadAdminLogs();
        } else if (section === 'export') {
            // Export handled separately
        }
    } catch (error) {
        handleError(error);
    }
}

async function loadNotifications() {
    const response = await fetch(`${API_URL}?action=get_notifications`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        document.getElementById('notifications').innerHTML = result.data
            .map(n => `<div class="notification">${n.message}</div>`)
            .join('');
    }
}

async function loadAnalytics() {
    const response = await fetch(`${API_URL}?action=get_analytics`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const { total_users, total_orders, total_products, total_sales, sales_by_category, top_products } = result.data;
        document.getElementById('total-users').textContent = total_users;
        document.getElementById('total-orders').textContent = total_orders;
        document.getElementById('total-products').textContent = total_products;
        document.getElementById('total-sales').textContent = `${total_sales || 0} ₽`;

        new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: sales_by_category.map(c => c.category_name),
                datasets: [{
                    label: 'Продажи по категориям',
                    data: sales_by_category.map(c => c.total_sales),
                    backgroundColor: '#3498db'
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('topProductsChart'), {
            type: 'pie',
            data: {
                labels: top_products.map(p => p.name),
                datasets: [{
                    label: 'Топ-продукты',
                    data: top_products.map(p => p.total_sales),
                    backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6']
                }]
            }
        });
    }
}

async function loadUsers() {
    const search = document.getElementById('users-search').value;
    const response = await fetch(`${API_URL}?action=get_users&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#users-table tbody');
        tbody.innerHTML = result.data.map(user => `
            <tr>
                <td>${user.user_id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.role_name}</td>
                <td>${user.phone || '-'}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_user', ${user.user_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_user', ${user.user_id}, 'users')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'users');
    }
}

async function loadOrders() {
    const search = document.getElementById('orders-search').value;
    const response = await fetch(`${API_URL}?action=get_orders&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#orders-table tbody');
        tbody.innerHTML = result.data.map(order => `
            <tr>
                <td>${order.order_id}</td>
                <td>${order.order_code}</td>
                <td>${order.user_name}</td>
                <td>${order.order_status}</td>
                <td>${order.total_price} ₽</td>
                <td>
                    <button class="edit" onclick="openModal('edit_order', ${order.order_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_order', ${order.order_id}, 'orders')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'orders');
    }
}

async function loadProducts() {
    const search = document.getElementById('products-search').value;
    const category = document.getElementById('products-category-filter').value;
    const url = `${API_URL}?action=get_products&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}${category ? `&category_id=${category}` : ''}`;
    const response = await fetch(url, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#products-table tbody');
        tbody.innerHTML = result.data.map(product => `
            <tr>
                <td>${product.product_id}</td>
                <td>${product.name}</td>
                <td>${product.category_name || '-'}</td>
                <td>${product.price} ₽</td>
                <td>${product.stock_quantity}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_product', ${product.product_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_product', ${product.product_id}, 'products')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'products');
        await loadCategoryFilter('products');
    }
}

async function loadCategories() {
    const search = document.getElementById('categories-search').value;
    const response = await fetch(`${API_URL}?action=get_categories&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#categories-table tbody');
        tbody.innerHTML = result.data.map(category => `
            <tr>
                <td>${category.category_id}</td>
                <td>${category.name}</td>
                <td>${category.parent_name || '-'}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_category', ${category.category_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_category', ${category.category_id}, 'categories')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'categories');
    }
}

async function loadCharacteristics() {
    const search = document.getElementById('characteristics-search').value;
    const category = document.getElementById('characteristics-category-filter').value;
    const url = `${API_URL}?action=get_characteristics&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}${category ? `&category_id=${category}` : ''}`;
    const response = await fetch(url, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#characteristics-table tbody');
        tbody.innerHTML = result.data.map(char => `
            <tr>
                <td>${char.characteristic_id}</td>
                <td>${char.name}</td>
                <td>${char.value_type}</td>
                <td>${char.category_name || '-'}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_characteristic', ${char.characteristic_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_characteristic', ${char.characteristic_id}, 'characteristics')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'characteristics');
        await loadCategoryFilter('characteristics');
    }
}

async function loadNews() {
    const search = document.getElementById('news-search').value;
    const response = await fetch(`${API_URL}?action=get_news&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#news-table tbody');
        tbody.innerHTML = result.data.map(news => `
            <tr>
                <td>${news.news_id}</td>
                <td>${news.title}</td>
                <td>${new Date(news.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_news', ${news.news_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_news', ${news.news_id}, 'news')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'news');
    }
}

async function loadPromotions() {
    const search = document.getElementById('promotions-search').value;
    const response = await fetch(`${API_URL}?action=get_promotions&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#promotions-table tbody');
        tbody.innerHTML = result.data.map(promo => `
            <tr>
                <td>${promo.promotion_id}</td>
                <td>${promo.title}</td>
                <td>${promo.status}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_promotion', ${promo.promotion_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_promotion', ${promo.promotion_id}, 'promotions')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'promotions');
    }
}

async function loadCarts() {
    const search = document.getElementById('carts-search').value;
    const response = await fetch(`${API_URL}?action=get_carts&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#carts-table tbody');
        tbody.innerHTML = result.data.map(cart => `
            <tr>
                <td>${cart.cart_id}</td>
                <td>${cart.user_name}</td>
                <td>${cart.user_email}</td>
                <td>
                    <button class="delete" onclick="deleteItem('delete_cart', ${cart.cart_id}, 'carts')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'carts');
    }
}

async function loadComparisonLists() {
    const search = document.getElementById('comparison_lists-search').value;
    const response = await fetch(`${API_URL}?action=get_comparison_lists&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#comparison_lists-table tbody');
        tbody.innerHTML = result.data.map(list => `
            <tr>
                <td>${list.comparison_id}</td>
                <td>${list.user_name}</td>
                <td>${list.user_email}</td>
                <td>
                    <button class="delete" onclick="deleteItem('delete_comparison_list', ${list.comparison_id}, 'comparison_lists')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'comparison_lists');
    }
}

async function loadDeliveryStatuses() {
    const search = document.getElementById('delivery_statuses-search').value;
    const response = await fetch(`${API_URL}?action=get_delivery_statuses&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#delivery_statuses-table tbody');
        tbody.innerHTML = result.data.map(status => `
            <tr>
                <td>${status.delivery_status_id}</td>
                <td>${status.order_code}</td>
                <td>${status.status}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_delivery_status', ${status.delivery_status_id})">Редактировать</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'delivery_statuses');
    }
}

async function loadFavorites() {
    const search = document.getElementById('favorites-search').value;
    const response = await fetch(`${API_URL}?action=get_favorites&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#favorites-table tbody');
        tbody.innerHTML = result.data.map(favorite => `
            <tr>
                <td>${favorite.favorite_id}</td>
                <td>${favorite.user_name}</td>
                <td>${favorite.product_name}</td>
                <td>
                    <button class="delete" onclick="deleteItem('delete_favorite', ${favorite.favorite_id}, 'favorites')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'favorites');
    }
}

async function loadFeedback() {
    const search = document.getElementById('feedback-search').value;
    const category = document.getElementById('feedback-category-filter').value;
    const status = document.getElementById('feedback-status-filter').value;
    const url = `${API_URL}?action=get_feedback&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}${category ? `&category=${category}` : ''}${status ? `&status=${status}` : ''}`;
    const response = await fetch(url, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#feedback-table tbody');
        tbody.innerHTML = result.data.map(feedback => `
            <tr>
                <td>${feedback.feedback_id}</td>
                <td>${feedback.user_name || '-'}</td>
                <td>${feedback.category}</td>
                <td>${feedback.status}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_feedback', ${feedback.feedback_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_feedback', ${feedback.feedback_id}, 'feedback')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'feedback');
    }
}

async function loadLogs() {
    const search = document.getElementById('logs-search').value;
    const response = await fetch(`${API_URL}?action=get_logs&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#logs-table tbody');
        tbody.innerHTML = result.data.map(log => `
            <tr>
                <td>${log.log_id}</td>
                <td>${log.user_name}</td>
                <td>${log.action}</td>
                <td>${new Date(log.created_at).toLocaleString()}</td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'logs');
    }
}

async function loadRecommendations() {
    const search = document.getElementById('recommendations-search').value;
    const response = await fetch(`${API_URL}?action=get_recommendations&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#recommendations-table tbody');
        tbody.innerHTML = result.data.map(rec => `
            <tr>
                <td>${rec.recommendation_id}</td>
                <td>${rec.user_name}</td>
                <td>${rec.product_name}</td>
                <td>
                    <button class="delete" onclick="deleteItem('delete_recommendation', ${rec.recommendation_id}, 'recommendations')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'recommendations');
    }
}

async function loadReviews() {
    const search = document.getElementById('reviews-search').value;
    const response = await fetch(`${API_URL}?action=get_reviews&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#reviews-table tbody');
        tbody.innerHTML = result.data.map(review => `
            <tr>
                <td>${review.review_id}</td>
                <td>${review.user_name}</td>
                <td>${review.product_name}</td>
                <td>${review.rating}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_review', ${review.review_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_review', ${review.review_id}, 'reviews')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'reviews');
    }
}

async function loadStores() {
    const search = document.getElementById('stores-search').value;
    const response = await fetch(`${API_URL}?action=get_stores&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#stores-table tbody');
        tbody.innerHTML = result.data.map(store => `
            <tr>
                <td>${store.store_id}</td>
                <td>${store.name}</td>
                <td>${store.address}</td>
                <td>${store.working_hours}</td>
                <td>
                    <button class="edit" onclick="openModal('edit_store', ${store.store_id})">Редактировать</button>
                    <button class="delete" onclick="deleteItem('delete_store', ${store.store_id}, 'stores')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'stores');
    }
}

async function loadUserSessions() {
    const search = document.getElementById('user_sessions-search').value;
    const response = await fetch(`${API_URL}?action=get_user_sessions&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#user_sessions-table tbody');
        tbody.innerHTML = result.data.map(session => `
            <tr>
                <td>${session.session_id}</td>
                <td>${session.user_name}</td>
                <td>${session.token.substring(0, 10)}...</td>
                <td>${new Date(session.expires_at).toLocaleString()}</td>
                <td>
                    <button class="delete" onclick="deleteItem('delete_user_session', ${session.session_id}, 'user_sessions')">Удалить</button>
                </td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'user_sessions');
    }
}

async function loadAdminLogs() {
    const search = document.getElementById('admin_logs-search').value;
    const response = await fetch(`${API_URL}?action=get_admin_logs&search=${encodeURIComponent(search)}&page=${currentPage}&per_page=${perPage}`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const tbody = document.querySelector('#admin_logs-table tbody');
        tbody.innerHTML = result.data.map(log => `
            <tr>
                <td>${log.log_id}</td>
                <td>${log.admin_name}</td>
                <td>${log.action}</td>
                <td>${new Date(log.created_at).toLocaleString()}</td>
            </tr>
        `).join('');
        renderPagination(result.pagination, 'admin_logs');
    }
}

async function loadCategoryFilter(section) {
    const response = await fetch(`${API_URL}?action=get_categories&search=&page=1&per_page=1000`, { credentials: 'include' });
    const result = await response.json();
    if (result.status === 'success') {
        const select = document.getElementById(`${section}-category-filter`);
        select.innerHTML = '<option value="">Все категории</option>' + 
            result.data.map(cat => `<option value="${cat.category_id}">${cat.name}</option>`).join('');
    }
}

function renderPagination(pagination, section) {
    const paginationDiv = document.getElementById(`${section}-pagination`);
    paginationDiv.innerHTML = '';
    for (let i = 1; i <= pagination.total_pages; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        if (i === pagination.page) button.classList.add('active');
        button.addEventListener('click', () => {
            currentPage = i;
            loadSection(section);
        });
        paginationDiv.appendChild(button);
    }
}

async function openModal(action, id = null) {
    const modal = document.getElementById('modal');
    const form = document.getElementById('modal-form');
    form.innerHTML = '';
    let data = {};

    if (action.startsWith('edit_')) {
        const entity = action.replace('edit_', '');
        const response = await fetch(`${API_URL}?action=get_${entity}s&search=&page=1&per_page=1`, { credentials: 'include' });
        const result = await response.json();
        data = result.data.find(item => item[`${entity}_id`] === id) || {};
    }

    if (action === 'add_user' || action === 'edit_user') {
        const rolesResponse = await fetch(`${API_URL}?action=get_roles`, { credentials: 'include' });
        const rolesResult = await rolesResponse.json();
        form.innerHTML = `
            <label>Имя</label>
            <input type="text" name="name" value="${data.name || ''}" required>
            <label>Email</label>
            <input type="email" name="email" value="${data.email || ''}" required>
            <label>Роль</label>
            <select name="role_id" required>
                <option value="">Выберите роль</option>
                ${rolesResult.data.map(role => `<option value="${role.id}" ${data.role_id === role.id ? 'selected' : ''}>${role.name}</option>`).join('')}
            </select>
            <label>Телефон</label>
            <input type="text" name="phone" value="${data.phone || ''}">
            <label>Почтовый индекс</label>
            <input type="text" name="postal_code" value="${data.postal_code || ''}">
            <label>Способ оплаты</label>
            <input type="text" name="preferred_payment_method" value="${data.preferred_payment_method || ''}">
            <label>Способ доставки</label>
            <input type="text" name="preferred_delivery_method" value="${data.preferred_delivery_method || ''}">
            <button type="submit">${action === 'add_user' ? 'Добавить' : 'Сохранить'}</button>
        `;
    } else if (action === 'add_order' || action === 'edit_order') {
        form.innerHTML = `
            <label>Статус</label>
            <select name="order_status" required>
                <option value="Принят" ${data.order_status === 'Принят' ? 'selected' : ''}>Принят</option>
                <option value="В процессе доставки" ${data.order_status === 'В процессе доставки' ? 'selected' : ''}>В процессе доставки</option>
                <option value="Доставлен" ${data.order_status === 'Доставлен' ? 'selected' : ''}>Доставлен</option>
                <option value="Возврат" ${data.order_status === 'Возврат' ? 'selected' : ''}>Возврат</option>
            </select>
            <label>Адрес доставки</label>
            <input type="text" name="delivery_address" value="${data.delivery_address || ''}">
            <button type="submit">${action === 'add_order' ? 'Добавить' : 'Сохранить'}</button>
        `;
    } else if (action === 'add_product' || action === 'edit_product') {
        const categoriesResponse = await fetch(`${API_URL}?action=get_categories&search=&page=1&per_page=1000`, { credentials: 'include' });
        const categoriesResult = await categoriesResponse.json();
        let characteristicsHtml = '';
        if (data.category_id) {
            const charsResponse = await fetch(`${API_URL}?action=get_characteristics&category_id=${data.category_id}`, { credentials: 'include' });
            const charsResult = await charsResponse.json();
            characteristicsHtml = charsResult.data.map(char => `
                <label>${char.name} (${char.value_type})</label>
                <input type="${char.value_type === 'Число' ? 'number' : 'text'}" name="characteristic_${char.characteristic_id}" value="${data.characteristics?.find(c => c.characteristic_id === char.characteristic_id)?.value || ''}">
            `).join('');
        }
        form.innerHTML = `
            <label>Название</label>
            <input type="text" name="name" value="${data.name || ''}" required>
            <label>Описание</label>
            <textarea name="description">${data.description || ''}</textarea>
            <label>Цена</label>
            <input type="number" name="price" value="${data.price || ''}" required>
            <label>Количество на складе</label>
            <input type="number" name="stock_quantity" value="${data.stock_quantity || ''}" required>
            <label>Категория</label>
            <select name="category_id">
                <option value="">Без категории</option>
                ${categoriesResult.data.map(cat => `<option value="${cat.category_id}" ${data.category_id === cat.category_id ? 'selected' : ''}>${cat.name}</option>`).join('')}
            </select>
            <label>Изображение</label>
            <input type="file" name="image" accept="image/*">
            <label><input type="checkbox" name="is_bestseller" ${data.is_bestseller ? 'checked' : ''}> Бестселлер</label>
            <label><input type="checkbox" name="is_new" ${data.is_new ? 'checked' : ''}> Новинка</label>
            <label>Скидка (%)</label>
            <input type="number" name="discount" value="${data.discount || '0'}">
            ${characteristicsHtml}
            <button type="submit">${action === 'add_product' ? 'Добавить' : 'Сохранить'}</button>
        `;
    } else if (action === 'add_category' || action === 'edit_category') {
        const categoriesResponse = await fetch(`${API_URL}?action=get_categories&search=&page=1&per_page=1000`, { credentials: 'include' });
        const categoriesResult = await categoriesResponse.json();
        form.innerHTML = `
            <label>Название</label>
            <input type="text" name="name" value="${data.name || ''}" required>
            <label>Родительская категория</label>
            <select name="parent_category_id">
                <option value="">Нет</option>
                ${categoriesResult.data.map(cat => `<option value="${cat.category_id}" ${data.parent_category_id === cat.category_id ? 'selected' : ''}>${cat.name}</option>`).join('')}
            </select>
            <button type="submit">${action === 'add_category' ? 'Добавить' : 'Сохранить'}</button>
        `;
    } else if (action === 'add_characteristic' || action === 'edit_characteristic') {
        const categoriesResponse = await fetch(`${API_URL}?action=get_categories&search=&page=1&per_page=1000`, { credentials: 'include' });
        const categoriesResult = await categoriesResponse.json();
        form.innerHTML = `
            <label>Название</label>
            <input type="text" name="name" value="${data.name || ''}" required>
            <label>Тип значения</label>
            <select name="value_type" required>
                <option value="Текст" ${data.value_type === 'Текст' ? 'selected' : ''}>Текст</option>
                <option value="Число" ${data.value_type === 'Число' ? 'selected' : ''}>Число</option>
            </select>
            <label>Категория</label>
            <select name="category_id" required>
                <option value="">Выберите категорию</option>
                ${categoriesResult.data.map(cat => `<option value="${cat.category_id}" ${data.category_id === cat.category_id ? 'selected' : ''}>${cat.name}</option>`).join('')}
            </select>
            <button type="submit">${action === 'add_characteristic' ? 'Добавить' : 'Сохранить'}</button>
        `;
    } else if (action === 'add_news' || action === 'edit_news') {
        form.innerHTML = `
            <label>Заголовок</label>
            <input type="text" name="title" value="${data.title || ''}" required>
            <label>Контент</label>
            <textarea name="content" required>${data.content || ''}</textarea>
            <label>Изображение</label>
            <input type="file" name="image" accept="image/*">
            <button type="submit">${action === 'add_news' ? 'Добавить' : 'Сохранить'}</button>
        `;
    } else if (action === 'add_promotion' || action === 'edit_promotion') {
        const categoriesResponse = await fetch(`${API_URL}?action=get_categories&search=&page=1&per_page=1000`, { credentials: 'include' });
        const categoriesResult = await categoriesResponse.json();
        form.innerHTML = `
            <label>Заголовок</label>
            <input type="text" name="title" value="${data.title || ''}" required>
            <label>Описание</label>
            <textarea name="description">${data.description || ''}</textarea>
            <label>Дата начала</label>
            <input type="date" name="start_date" value="${data.start_date?.split(' ')[0] || ''}" required>
            <label>Дата окончания</label>
            <input type="date" name="end_date" value="${data.end_date?.split(' ')[0] || ''}" required>
            <label>Категория</label>
            <select name="category_id">
                <option value="">Без категории</option>
                ${categoriesResult.data.map(cat => `<option value="${cat.category_id}" ${data.category_id === cat.category_id ? 'selected' : ''}>${cat.name}</option>`).join('')}
            </select>
            <label>Изображение</label>
            <input type="file" name="image" accept="image/*">
            <button type="submit">${action === 'add_promotion' ? 'Добавить' : 'Сохранить'}</button>
        `;
    } else if (action === 'edit_delivery_status') {
        form.innerHTML = `
            <label>Статус</label>
            <select name="status" required>
                <option value="Принят" ${data.status === 'Принят' ? 'selected' : ''}>Принят</option>
                <option value="В процессе доставки" ${data.status === 'В процессе доставки' ? 'selected' : ''}>В процессе доставки</option>
                <option value="Доставлен" ${data.status === 'Доставлен' ? 'selected' : ''}>Доставлен</option>
                <option value="Возврат" ${data.status === 'Возврат' ? 'selected' : ''}>Возврат</option>
            </select>
            <button type="submit">Сохранить</button>
        `;
    } else if (action === 'edit_feedback') {
        form.innerHTML = `
            <label>Категория</label>
            <select name="category" required>
                <option value="Техподдержка" ${data.category === 'Техподдержка' ? 'selected' : ''}>Техподдержка</option>
                <option value="Заказ" ${data.category === 'Заказ' ? 'selected' : ''}>Заказ</option>
                <option value="Другое" ${data.category === 'Другое' ? 'selected' : ''}>Другое</option>
            </select>
            <label>Ответ</label>
            <textarea name="response">${data.response || ''}</textarea>
            <label>Статус</label>
            <select name="status" required>
                <option value="Зарегистрирован" ${data.status === 'Зарегистрирован' ? 'selected' : ''}>Зарегистрирован</option>
                <option value="В обработке" ${data.status === 'В обработке' ? 'selected' : ''}>В обработке</option>
                <option value="Закрыт" ${data.status === 'Закрыт' ? 'selected' : ''}>Закрыт</option>
            </select>
            <button type="submit">Сохранить</button>
        `;
    } else if (action === 'edit_review') {
        form.innerHTML = `
            <label>Рейтинг</label>
            <input type="number" name="rating" value="${data.rating || ''}" min="1" max="5" required>
            <label>Комментарий</label>
            <textarea name="comment">${data.comment || ''}</textarea>
            <button type="submit">Сохранить</button>
        `;
    } else if (action === 'add_store' || action === 'edit_store') {
        form.innerHTML = `
            <label>Название</label>
            <input type="text" name="name" value="${data.name || ''}" required>
            <label>Адрес</label>
            <input type="text" name="address" value="${data.address || ''}" required>
            <label>Широта</label>
            <input type="number" name="latitude" step="any" value="${data.latitude || ''}">
            <label>Долгота</label>
            <input type="number" name="longitude" step="any" value="${data.longitude || ''}">
            <label>Часы работы</label>
            <input type="text" name="working_hours" value="${data.working_hours || ''}" required>
            <button type="submit">${action === 'add_store' ? 'Добавить' : 'Сохранить'}</button>
        `;
    }

    if (id) {
        form.innerHTML += `<input type="hidden" name="${action.replace('edit_', '')}_id" value="${id}">`;
    }

    form.onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        formData.append('action', action);
        await submitForm(formData);
    };

    modal.style.display = 'block';
}

async function submitForm(formData) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        const result = await response.json();
        if (result.status === 'success') {
            closeModal();
            loadSection(currentSection);
            alert(result.message);
        } else {
            alert(result.message);
        }
    } catch (error) {
        handleError(error);
    }
}

async function deleteItem(action, id, section) {
    if (confirm('Вы уверены, что хотите удалить эту запись?')) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append(`${action.replace('delete_', '')}_id`, id);
        await submitForm(formData);
    }
}

async function exportData() {
    const table = document.getElementById('export-table').value;
    window.location.href = `${API_URL}?action=export_data&table=${table}&format=csv`;
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function handleError(error) {
    console.error(error);
    if (error.message.includes('401') || error.message.includes('403')) {
        window.location.href = '/login.html';
    } else {
        alert('Ошибка: ' + error.message);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}