function loadTableData(table) {
    const tbody = document.getElementById(`${table}-table`);
    tbody.innerHTML = '<tr><td colspan="100">Загрузка...</td></tr>';

    fetch(`./api/index.php?action=analytics&table=${table}`, { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            console.log(`Данные для таблицы ${table}:`, data);
            if (data.error) {
                console.error(`Ошибка для таблицы ${table}:`, data.error);
                alert(data.error);
                return;
            }
            if (data.message) {
                console.warn(`Сообщение для таблицы ${table}:`, data.message);
                tbody.innerHTML = `<tr><td colspan="100">${data.message}</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            data.forEach(row => {
                console.log(`Строка таблицы ${table}:`, row);
                const tr = document.createElement('tr');
                Object.keys(row).forEach(key => {
                    if (key === '_id' || key === 'password_hash' || key === 'token' || key.endsWith('_id') || key === 'image_url' || key === 'photo' || key === 'phone' || key === 'postal_code' || key === 'preferred_payment_method' || key === 'preferred_delivery_method') return;
                    const td = document.createElement('td');
                    td.textContent = row[key] ?? '—';
                    tr.appendChild(td);
                });
                const actions = document.createElement('td');
                if (table === 'Reviews') {
                    actions.innerHTML = `
                        <button onclick="moderateReview(${row._id}, 'Одобрен')">Одобрить</button>
                        <button onclick="moderateReview(${row._id}, 'Отклонен')">Отклонить</button>
                    `;
                } else {
                    actions.innerHTML = `
                        <button onclick="openModal('edit', '${table}', ${row._id})">Редактировать</button>
                        <button onclick="deleteRecord('${table}', ${row._id})">Удалить</button>
                        ${table === 'Role' ? `<button onclick="openPermissionsModal(${row._id})">Права</button>` : ''}
                    `;
                }
                tr.appendChild(actions);
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error(`Ошибка загрузки данных для таблицы ${table}:`, error);
            tbody.innerHTML = `<tr><td colspan="100">Ошибка загрузки данных: ${error}</td></tr>`;
        });
}

function openModal(action, table, id = null) {
    const modal = document.getElementById('modal');
    const title = document.getElementById('modal-title');
    const form = document.getElementById('modal-form');
    title.textContent = action === 'create' ? `Добавить в ${table}` : `Редактировать ${table}`;
    form.innerHTML = '';

    // Добавляем кнопку закрытия модального окна
    const closeButton = document.createElement('button');
    closeButton.textContent = 'Закрыть';
    closeButton.onclick = () => modal.style.display = 'none';
    form.appendChild(closeButton);

    fetch(`./api/index.php?action=analytics&table=${table}`, { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            console.log(`Структура таблицы ${table} для модального окна:`, data);
            if (data.error) {
                console.error(`Ошибка структуры таблицы ${table}:`, data.error);
                alert(data.error);
                return;
            }
            if (data.message) {
                console.warn(`Сообщение структуры таблицы ${table}:`, data.message);
                alert(data.message);
                return;
            }
            const fields = Object.keys(data[0] || {});
            console.log(`Поля таблицы ${table}:`, fields);

            // Поля, которые нужно переводить на русский
            const fieldTranslations = {
                'product_name': 'Товар',
                'user_email': 'Пользователь',
                'rating': 'Рейтинг',
                'comment': 'Комментарий',
                'status': 'Статус',
                'name': 'Имя',
                'email': 'Email',
                'role_name': 'Роль',
                'order_code': 'Код заказа',
                'order_status': 'Статус заказа',
                'delivery_address': 'Адрес доставки',
                'total_price': 'Общая стоимость',
                'created_at': 'Дата создания',
                'description': 'Описание',
                'price': 'Цена',
                'stock_quantity': 'Количество на складе',
                'is_bestseller': 'Бестселлер',
                'is_new': 'Новинка',
                'discount': 'Скидка',
                'title': 'Заголовок',
                'content': 'Содержание',
                'start_date': 'Дата начала',
                'end_date': 'Дата окончания',
                'quantity': 'Количество',
                'action': 'Действие',
                'table_name': 'Таблица',
                'record_id': 'ID записи',
                'action_type': 'Тип действия',
                'admin_id': 'Администратор',
                'action_description': 'Описание действия',
                'message': 'Сообщение',
                'category': 'Категория',
                'value_type': 'Тип значения',
                'value': 'Значение',
                'price_per_item': 'Цена за единицу',
                'updated_at': 'Дата обновления',
                'address': 'Адрес',
                'phone': 'Телефон',
                'working_hours': 'Часы работы',
                'expires_at': 'Дата истечения',
                'image_url': 'Фото'
            };

            fields.forEach(field => {
                if (field === '_id' || field === 'password_hash' || field === 'token' || field.endsWith('_id') || field === 'phone' || field === 'postal_code' || field === 'preferred_payment_method' || field === 'preferred_delivery_method') return;

                const div = document.createElement('div');

                // Специальная логика для поля role_name (выпадающий список)
                if (field === 'role_name' && table === 'Users') {
                    div.innerHTML = `
                        <label>Роль</label>
                        <select name="role_id"></select>
                    `;
                    const select = div.querySelector('select');
                    // Загружаем доступные роли
                    fetch(`./api/index.php?action=analytics&table=Role`, { credentials: 'include' })
                        .then(res => res.json())
                        .then(roles => {
                            roles.forEach(role => {
                                const option = document.createElement('option');
                                option.value = role._id;
                                option.textContent = role.Name;
                                select.appendChild(option);
                            });
                        });
                }
                // Специальная логика для статусов (выпадающий список)
                else if (field === 'status' && table === 'Users') {
                    div.innerHTML = `
                        <label>Статус</label>
                        <select name="status">
                            <option value="Онлайн">Онлайн</option>
                            <option value="Не в сети">Не в сети</option>
                        </select>
                    `;
                }
                else if (field === 'status' && table === 'Reviews') {
                    div.innerHTML = `
                        <label>Статус</label>
                        <select name="status">
                            <option value="На модерации">На модерации</option>
                            <option value="Одобрен">Одобрен</option>
                            <option value="Отклонен">Отклонен</option>
                        </select>
                    `;
                }
                else if (field === 'order_status' && table === 'Orders') {
                    div.innerHTML = `
                        <label>Статус заказа</label>
                        <select name="order_status">
                            <option value="Новый">Новый</option>
                            <option value="В обработке">В обработке</option>
                            <option value="Доставлен">Доставлен</option>
                            <option value="Отменен">Отменен</option>
                            <option value="Отправлен">Отправлен</option>
                            <option value="В ожидании">В ожидании</option>
                        </select>
                    `;
                }
                else if (field === 'status' && table === 'Promotions') {
                    div.innerHTML = `
                        <label>Статус</label>
                        <select name="status">
                            <option value="Активна">Активна</option>
                            <option value="Завершена">Завершена</option>
                        </select>
                    `;
                }
                // Поле для загрузки фото
                else if (field === 'image_url' && (table === 'News' || table === 'Products' || table === 'Promotions')) {
                    div.innerHTML = `
                        <label>Фото</label>
                        <input type="file" name="image_url" accept="image/*">
                    `;
                }
                // Поле для булевых значений (is_bestseller, is_new)
                else if (field === 'is_bestseller' || field === 'is_new') {
                    div.innerHTML = `
                        <label>${fieldTranslations[field]}</label>
                        <select name="${field}">
                            <option value="1">Да</option>
                            <option value="0">Нет</option>
                        </select>
                    `;
                }
                else {
                    div.innerHTML = `
                        <label>${fieldTranslations[field] || field}</label>
                        <input name="${field}" placeholder="${fieldTranslations[field] || field}" type="text">
                    `;
                }
                form.appendChild(div);
            });

            const submit = document.createElement('button');
            submit.textContent = 'Сохранить';
            submit.onclick = () => saveRecord(action, table, id);
            form.appendChild(submit);
            modal.style.display = 'block';

            if (action === 'edit' && id) {
                fetch(`./api/index.php?action=analytics&table=${table}&id=${id}`, { credentials: 'include' })
                    .then(response => response.json())
                    .then(row => {
                        console.log(`Данные для редактирования в таблице ${table} (ID: ${id}):`, row);
                        if (row.error) {
                            console.error(`Ошибка редактирования в таблице ${table}:`, row.error);
                            alert(row.error);
                            return;
                        }
                        if (!row[0]) {
                            console.warn(`Запись не найдена в таблице ${table} (ID: ${id})`);
                            alert('Запись не найдена');
                            return;
                        }
                        Object.entries(row[0]).forEach(([key, value]) => {
                            if (key === '_id' || key === 'password_hash' || key === 'token' || key.endsWith('_id') || key === 'image_url' || key === 'photo' || key === 'phone' || key === 'postal_code' || key === 'preferred_payment_method' || key === 'preferred_delivery_method') return;
                            const input = form.querySelector(`[name="${key}"]`);
                            if (input) {
                                if (input.tagName === 'SELECT') {
                                    // Для выпадающих списков (например, role_name, status)
                                    if (key === 'role_name') {
                                        // Для role_name нужно установить role_id
                                        fetch(`./api/index.php?action=analytics&table=Role`, { credentials: 'include' })
                                            .then(res => res.json())
                                            .then(roles => {
                                                const role = roles.find(r => r.Name === value);
                                                if (role) {
                                                    input.value = role._id;
                                                }
                                            });
                                    } else {
                                        input.value = value ?? '';
                                    }
                                } else {
                                    input.value = value ?? '';
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error(`Ошибка загрузки данных для редактирования в таблице ${table}:`, error);
                        alert('Ошибка загрузки данных: ' + error);
                    });
            }
        })
        .catch(error => {
            console.error(`Ошибка загрузки структуры таблицы ${table}:`, error);
            alert('Ошибка загрузки структуры таблицы: ' + error);
        });
}

function saveRecord(action, table, id) {
    const form = document.getElementById('modal-form');
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        if (key === 'image_url' && value instanceof File && value.size > 0) {
            // Обработка файла
            data[key] = value;
        } else if (key !== 'image_url') {
            data[key] = value;
        }
    });

    const url = action === 'create'
        ? `./api/index.php?action=create&table=${table}`
        : `./api/index.php?action=update&table=${table}&id=${id}`;

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
        .then(response => response.json())
        .then(result => {
            if (result.error) {
                alert(result.error);
            } else {
                alert('Успешно сохранено!');
                document.getElementById('modal').style.display = 'none';
                loadTableData(table);
            }
        })
        .catch(error => {
            console.error('Ошибка сохранения:', error);
            alert('Ошибка сохранения: ' + error);
        });
}
function openCharacteristicsModal() {
    const modal = document.getElementById('modal');
    const title = document.getElementById('modal-title');
    const form = document.getElementById('modal-form');
    title.textContent = 'Управление характеристиками';
    form.innerHTML = '';

    const closeButton = document.createElement('button');
    closeButton.textContent = 'Закрыть';
    closeButton.onclick = () => modal.style.display = 'none';
    form.appendChild(closeButton);

    // Выбор категории
    const categoryDiv = document.createElement('div');
    categoryDiv.innerHTML = `
        <label>Категория</label>
        <select name="category_id"></select>
    `;
    const categorySelect = categoryDiv.querySelector('select');
    fetch(`./api/index.php?action=analytics&table=Categories`, { credentials: 'include' })
        .then(res => res.json())
        .then(categories => {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category._id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        });
    form.appendChild(categoryDiv);

    // Выбор характеристики
    const charDiv = document.createElement('div');
    charDiv.innerHTML = `
        <label>Характеристика</label>
        <select name="characteristic_id"></select>
    `;
    const charSelect = charDiv.querySelector('select');
    categorySelect.onchange = () => {
        const categoryId = categorySelect.value;
        charSelect.innerHTML = '';
        fetch(`./api/index.php?action=analytics&table=Product_Characteristics&category_id=${categoryId}`, { credentials: 'include' })
            .then(res => res.json())
            .then(chars => {
                chars.forEach(char => {
                    const option = document.createElement('option');
                    option.value = char._id;
                    option.textContent = char.name;
                    charSelect.appendChild(option);
                });
            });
    };
    form.appendChild(charDiv);

    // Выбор продукта
    const productDiv = document.createElement('div');
    productDiv.innerHTML = `
        <label>Продукт</label>
        <select name="product_id"></select>
    `;
    const productSelect = productDiv.querySelector('select');
    categorySelect.onchange = () => {
        const categoryId = categorySelect.value;
        productSelect.innerHTML = '';
        fetch(`./api/index.php?action=analytics&table=Products&category_id=${categoryId}`, { credentials: 'include' })
            .then(res => res.json())
            .then(products => {
                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product._id;
                    option.textContent = product.name;
                    productSelect.appendChild(option);
                });
            });
    };
    form.appendChild(productDiv);

    // Поле для значения характеристики
    const valueDiv = document.createElement('div');
    valueDiv.innerHTML = `
        <label>Значение</label>
        <input name="value" type="text" placeholder="Значение">
    `;
    form.appendChild(valueDiv);

    const submit = document.createElement('button');
    submit.textContent = 'Сохранить';
    submit.onclick = () => {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        fetch(`./api/index.php?action=update_characteristic_value`, {
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include'
        })
            .then(response => response.json())
            .then(result => {
                if (result.error) {
                    alert(result.error);
                } else {
                    alert('Характеристика обновлена!');
                    modal.style.display = 'none';
                }
            });
    };
    form.appendChild(submit);

    modal.style.display = 'block';
}