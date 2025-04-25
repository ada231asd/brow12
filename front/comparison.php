<!DOCTYPE html>
<html>
<head>
    <title>Сравнение товаров</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #141414;
            color: white;
        }
        
        .comparison-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #141414;
            padding: 20px;
            border-radius: 8px;
        }
        
        h1 {
            color: white;
            margin-bottom: 20px;
        }
        
        .auth-message, .empty-message {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
            display: none;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .comparison-table th, .comparison-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .comparison-table th {
            background-color: #444444;
        }
        
        .characteristic-name {
            font-weight: bold;
            background-color: #444444;
        }
        
        td {
            background-color: #444444;
            color: white;
        }
        
        .remove-btn {
            color: #994dff;
            cursor: pointer;
            text-decoration: underline;
        }

        .product-image {
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0 auto 10px;
        }
    </style>
</head>
<body>
<?php require '../Header/header.html'; ?>
    <div class="comparison-container">
        <h1>Сравнение товаров</h1>
        <div class="auth-message">Пожалуйста, авторизуйтесь</div>
        <div class="empty-message">Нет товаров для сравнения</div>
        
        <table class="comparison-table" id="comparison-table"></table>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', async () => {
            const container = document.querySelector('.comparison-container');
            const authMessage = document.querySelector('.auth-message');
            const emptyMessage = document.querySelector('.empty-message');
            const comparisonTable = document.getElementById('comparison-table');
            
            let comparisonProducts = [];

            // Функция для рендера таблицы сравнения
            const renderComparisonTable = (products) => {
                const allCharacteristics = {};
                products.forEach(product => {
                    (product.characteristics || []).forEach(char => {
                        allCharacteristics[char.name] = true;
                    });
                });
                const characteristics = Object.keys(allCharacteristics);

                let html = `
                    <thead>
                        <tr>
                            <th class="characteristic-name">Характеристика</th>
                            ${products.map(p => `
                                <th>
                                    <div style="text-align: center;">
                                        <img src="../${p.image_url || ''}" 
                                             class="product-image"
                                             onerror="this.style.display='none'">
                                        <h3 style="margin: 5px 0;">${p.name}</h3>
                                        <div class="remove-btn" 
                                             data-product-id="${p.product_id}">
                                            Удалить
                                        </div>
                                    </div>
                                </th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>`;

                characteristics.forEach(charName => {
                    html += `<tr><td class="characteristic-name">${charName}</td>`;
                    products.forEach(product => {
                        const char = (product.characteristics || []).find(c => c.name === charName);
                        html += `<td>${char ? char.value : '-'}</td>`;
                    });
                    html += `</tr>`;
                });

                // Добавляем строку с ценами
                html += `<tr><td class="characteristic-name">Цена</td>`;
                products.forEach(product => {
                    const price = product.discount 
                        ? Math.round(product.price * (1 - product.discount/100))
                        : product.price;
                    html += `<td>${price.toLocaleString()} ₽</td>`;
                });
                html += `</tr></tbody>`;
                
                comparisonTable.innerHTML = html;
                setupRemoveButtons();
            };

            // Настройка кнопок удаления
            const setupRemoveButtons = () => {
                document.querySelectorAll('.remove-btn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        const productId = e.target.dataset.productId;
                        try {
                            await fetch('../api/add_to_comparison.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                credentials: 'include',
                                body: JSON.stringify({ product_id: productId })
                            });
                            
                            const newResponse = await fetch('../api/get_comparison.php', {
                                credentials: 'include'
                            });
                            const newData = await newResponse.json();
                            
                            if (newData.status === 'success') {
                                comparisonProducts = newData.data.products;
                                
                                if (comparisonProducts.length === 0) {
                                    comparisonTable.style.display = 'none';
                                    emptyMessage.style.display = 'block';
                                } else {
                                    renderComparisonTable(comparisonProducts);
                                }
                            }
                        } catch (error) {
                            console.error('Ошибка при удалении:', error);
                        }
                    });
                });
            };

            try {
                const response = await fetch('../api/get_comparison.php', {
                    credentials: 'include'
                });
                
                if (response.status === 401) {
                    throw new Error('401');
                }

                const data = await response.json();

                if (data.status === 'success') {
                    if (data.data.products.length === 0) {
                        emptyMessage.style.display = 'block';
                        comparisonTable.style.display = 'none';
                        return;
                    }

                    comparisonProducts = data.data.products;
                    renderComparisonTable(comparisonProducts);
                    
                } else {
                    alert(data.message);
                }
            } catch (error) {
                if (error.message === '401') {
                    authMessage.style.display = 'block';
                    container.style.display = 'none';
                } else {
                    console.error('Ошибка:', error);
                }
            }
        });
    </script>
</body>
</html>