<?php
// Подключение к базе данных (если нужно)
require_once 'ajax/db.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Управление товарами</title>
  <style>
    /* Основные стили */
body {
  font-family: 'Arial', sans-serif;
  margin: 0;
  padding: 0;
  background-color: #1a1a1a; /* Темный фон */
  color: #e0e0e0; /* Светлый текст */
}

.container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

h1 {
  color: #bb86fc; /* Фиолетовый акцент */
  text-align: center;
  margin-bottom: 20px;
}

/* Стили для списка товаров */
#products-list {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
}

.product-card {
  background: #2c2c2c; /* Темный фон карточки */
  border: 1px solid #444;
  border-radius: 10px;
  padding: 20px;
  width: 250px;
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 15px rgba(187, 134, 252, 0.3); /* Фиолетовая тень */
}

.image-container {
  width: 100%;
  height: 200px;
  background: #444; /* Серый фон для заглушки */
  border-radius: 10px;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.image-container img {
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
  border-radius: 10px;
}

.product-card h3 {
  color: #bb86fc; /* Фиолетовый акцент */
  margin: 10px 0;
}

.product-card p {
  margin: 5px 0;
  color: #e0e0e0;
}

.product-card button {
  background: #bb86fc; /* Фиолетовый акцент */
  color: #1a1a1a;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  margin: 5px;
  transition: background 0.3s ease;
}

.product-card button:hover {
  background: #9a67ea; /* Более темный фиолетовый */
}

/* Стили для модальных окон */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
}

.modal-content {
  background: #2c2c2c; /* Темный фон */
  margin: 10% auto;
  padding: 20px;
  border-radius: 10px;
  width: 50%;
  max-width: 600px;
  position: relative;
}

.modal-content h2 {
  color: #bb86fc; /* Фиолетовый акцент */
  margin-bottom: 20px;
}

.modal-content label {
  display: block;
  margin: 10px 0 5px;
  color: #e0e0e0;
}

.modal-content input,
.modal-content textarea,
.modal-content select {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #444;
  border-radius: 5px;
  background: #444;
  color: #e0e0e0;
}

.modal-content button {
  background: #bb86fc;
  color: #1a1a1a;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.modal-content button:hover {
  background: #9a67ea;
}

.close {
  color: #e0e0e0;
  font-size: 24px;
  font-weight: bold;
  cursor: pointer;
  position: absolute;
  right: 20px;
  top: 10px;
}

.close:hover {
  color: #bb86fc;
}

/* Стили для формы поиска */
#search {
  width: 100%;
  padding: 10px;
  margin-bottom: 20px;
  border: 1px solid #444;
  border-radius: 5px;
  background: #444;
  color: #e0e0e0;
}

/* Стили для кнопки "Добавить товар" */
button[onclick="openAddModal()"] {
  background: #bb86fc;
  color: #1a1a1a;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  margin-bottom: 20px;
  transition: background 0.3s ease;
}

button[onclick="openAddModal()"]:hover {
  background: #9a67ea;
}
  </style>
</head>
<body>
  <div class="container">
    <h1>Управление товарами</h1>
    <input type="text" id="search" placeholder="Поиск товаров..." oninput="searchProducts()">
    <button onclick="openAddModal()">Добавить товар</button>
    <div id="products-list"></div>
  </div>

  <!-- Модальное окно для добавления товара -->
  <div id="add-modal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeAddModal()">&times;</span>
      <h2>Добавить товар</h2>
      <form id="add-product-form">
        <label for="name">Наименование:</label>
        <input type="text" id="name" name="name" required>
        <label for="price">Цена:</label>
        <input type="number" id="price" name="price" required>
        <label for="description">Описание:</label>
        <textarea id="description" name="description" required></textarea>
        <label for="stock_quantity">Количество на складе:</label>
        <input type="number" id="stock_quantity" name="stock_quantity" required>
        <label for="category">Категория:</label>
        <select id="category" name="category" onchange="loadCharacteristics()" required>
          <option value="">Выберите категорию</option>
        </select>
        <div id="characteristics"></div>
        <label for="image">Фото:</label>
        <input type="file" id="image" name="image" accept="image/*">
        <button type="submit">Добавить</button>
      </form>
    </div>
  </div>

  <!-- Модальное окно для просмотра и редактирования товара -->
  <div id="details-modal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeDetailsModal()">&times;</span>
      <h2>Подробнее о товаре</h2>
      <div id="product-details"></div>
      <button onclick="openEditModal()">Изменить данные</button>
    </div>
  </div>

  <!-- Модальное окно для редактирования товара -->
  <div id="edit-modal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h2>Редактировать товар</h2>
      <form id="edit-product-form">
        <input type="hidden" id="edit-id" name="id">
        <label for="edit-name">Наименование:</label>
        <input type="text" id="edit-name" name="name" required>
        <label for="edit-price">Цена:</label>
        <input type="number" id="edit-price" name="price" required>
        <label for="edit-description">Описание:</label>
        <textarea id="edit-description" name="description" required></textarea>
        <label for="edit-stock_quantity">Количество на складе:</label>
        <input type="number" id="edit-stock_quantity" name="stock_quantity" required>
        <label for="edit-category">Категория:</label>
        <select id="edit-category" name="category" onchange="loadEditCharacteristics()" required>
          <option value="">Выберите категорию</option>
        </select>
        <div id="edit-characteristics"></div>
        <label for="edit-image">Фото:</label>
        <input type="file" id="edit-image" name="image" accept="image/*">
        <button type="submit">Сохранить</button>
      </form>
    </div>
  </div>

  <script>
    let currentProductId = null;

    document.addEventListener('DOMContentLoaded', () => {
      loadProducts();
      loadCategories();
    });

    // Загрузка товаров
    async function loadProducts() {
      const response = await fetch('ajax/products.php?action=getProducts');
      const products = await response.json();
      const productsList = document.getElementById('products-list');
      productsList.innerHTML = products.map(product => `
        <div class="product-card">
         <div class="image-container"> ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" onerror="this.style.display='none'">` : ''}</div>
          <h3>${product.name}</h3>

          <p>Цена: ${product.price} руб.</p>
          <p>Количество на складе: ${product.stock_quantity}</p>
          <button onclick="openDetailsModal(${product.product_id})">Подробнее</button>
          <button onclick="deleteProduct(${product.product_id})">Удалить</button>
        </div>
      `).join('');
    }

    // Поиск товаров
    async function searchProducts() {
      const query = document.getElementById('search').value;
      const response = await fetch(`ajax/products.php?action=searchProducts&query=${query}`);
      const products = await response.json();
      const productsList = document.getElementById('products-list');
      productsList.innerHTML = products.map(product => `
        <div class="product-card">
         <div class="image-container"> ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" onerror="this.style.display='none'">` : ''}</div>
          <h3>${product.name}</h3>

          <p>Цена: ${product.price} руб.</p>
          <p>Количество на складе: ${product.stock_quantity}</p>
          <button onclick="openDetailsModal(${product.product_id})">Подробнее</button>
          <button onclick="deleteProduct(${product.product_id})">Удалить</button>
        </div>
      `).join('');
    }

    // Удаление товара
    async function deleteProduct(id) {
      await fetch(`ajax/products.php?action=deleteProduct&id=${id}`, { method: 'DELETE' });
      loadProducts();
    }

    // Открытие модального окна с деталями товара
    async function openDetailsModal(id) {
      currentProductId = id;
      const response = await fetch(`ajax/products.php?action=getProductDetails&id=${id}`);
      const product = await response.json();
      const detailsModal = document.getElementById('details-modal');
      const productDetails = document.getElementById('product-details');

      // Форматируем характеристики в текстовый вид
      const characteristicsText = product.characteristics
        .map(char => `${char.name}: ${char.value}`)
        .join('<br>');

      productDetails.innerHTML = `
      <div class="image-container"> ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" onerror="this.style.display='none'">` : ''}</div>
        <h3>${product.name}</h3>
        <p>Цена: ${product.price} руб.</p>
        <p>Описание: ${product.description}</p>
        <p>Количество на складе: ${product.stock_quantity}</p>
        <p>Характеристики: <br>${characteristicsText}</p>
      `;
      detailsModal.style.display = 'block';
    }

    // Закрытие модальных окон
    function closeDetailsModal() {
      document.getElementById('details-modal').style.display = 'none';
    }
    function closeAddModal() {
      document.getElementById('add-modal').style.display = 'none';
    }
    function closeEditModal() {
      document.getElementById('edit-modal').style.display = 'none';
    }

    // Открытие модального окна добавления товара
    function openAddModal() {
      document.getElementById('add-modal').style.display = 'block';
    }

    // Добавление товара
    document.getElementById('add-product-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);

      try {
        const response = await fetch('ajax/products.php?action=addProduct', {
          method: 'POST',
          body: formData,
        });
        const result = await response.json();
        if (result.status === 'success') {
          closeAddModal();
          loadProducts();
        } else {
          alert('Ошибка при добавлении товара');
        }
      } catch (error) {
        console.error('Ошибка:', error);
      }
    });

    // Загрузка категорий
    async function loadCategories() {
      const response = await fetch('ajax/products.php?action=getCategories');
      const categories = await response.json();
      const categorySelect = document.getElementById('category');
      const editCategorySelect = document.getElementById('edit-category');
      categorySelect.innerHTML = categories.map(category => `
        <option value="${category.category_id}">${category.name}</option>
      `).join('');
      editCategorySelect.innerHTML = categories.map(category => `
        <option value="${category.category_id}">${category.name}</option>
      `).join('');
    }

    // Загрузка характеристик
    async function loadCharacteristics() {
      const categoryId = document.getElementById('category').value;
      const response = await fetch(`ajax/products.php?action=getCharacteristics&categoryId=${categoryId}`);
      const characteristics = await response.json();
      const characteristicsDiv = document.getElementById('characteristics');
      characteristicsDiv.innerHTML = characteristics.map(char => `
        <label for="${char.name}">${char.name}:</label>
        <input type="${char.value_type === 'Число' ? 'number' : 'text'}" id="${char.name}" name="${char.name}" required>
      `).join('');
    }

    // Открытие модального окна редактирования
    async function openEditModal() {
      const response = await fetch(`ajax/products.php?action=getProductDetails&id=${currentProductId}`);
      const product = await response.json();

      document.getElementById('edit-id').value = product.product_id;
      document.getElementById('edit-name').value = product.name;
      document.getElementById('edit-price').value = product.price;
      document.getElementById('edit-description').value = product.description;
      document.getElementById('edit-stock_quantity').value = product.stock_quantity;
      document.getElementById('edit-category').value = product.category_id;

      // Загрузка характеристик для редактирования
      await loadEditCharacteristics(product.category_id);

      // Заполнение характеристик
      const editCharacteristicsDiv = document.getElementById('edit-characteristics');
      editCharacteristicsDiv.innerHTML = product.characteristics
        .map(char => `
          <label for="edit-${char.name}">${char.name}:</label>
          <input type="text" id="edit-${char.name}" name="${char.name}" value="${char.value}" required>
        `)
        .join('');

      document.getElementById('edit-modal').style.display = 'block';
    }

    // Загрузка характеристик для редактирования
    async function loadEditCharacteristics(categoryId) {
      const response = await fetch(`ajax/products.php?action=getCharacteristics&categoryId=${categoryId}`);
      const characteristics = await response.json();
      const editCharacteristicsDiv = document.getElementById('edit-characteristics');
      editCharacteristicsDiv.innerHTML = characteristics.map(char => `
        <label for="edit-${char.name}">${char.name}:</label>
        <input type="${char.value_type === 'Число' ? 'number' : 'text'}" id="edit-${char.name}" name="${char.name}" required>
      `).join('');
    }

    // Отправка изменений
    document.getElementById('edit-product-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);

      try {
        const response = await fetch('ajax/products.php?action=updateProduct', {
          method: 'POST',
          body: formData,
        });
        const result = await response.json();
        if (result.status === 'success') {
          closeEditModal();
          loadProducts();
        } else {
          alert('Ошибка при обновлении товара');
        }
      } catch (error) {
        console.error('Ошибка:', error);
      }
    });
  </script>
</body>
</html>