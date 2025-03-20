<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Полная статистика магазина</title>
    <style>
    :root {
        --bg: #0f0a1a;
        --card-bg: #1e122f;
        --text: #f8f7fa;
        --accent: #8b5cf6;
        --accent-dark: #6d28d9;
        --warning: #ef4444;
        --success: #10b981;
        --border: #3e2a5c;
    }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: 'Segoe UI', sans-serif;
        padding: 1.5rem;
        margin: 0;
        min-height: 100vh;
    }

    h1 {
        text-align: center;
        margin: 1.5rem 0 2.5rem;
        font-size: 2.2rem;
        color: var(--accent);
        letter-spacing: -0.03em;
    }

    .grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1.2rem;
        max-width: 1600px;
        margin: 0 auto;
        justify-content: center;
    }

    .card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 1rem;
        flex: 1 1 260px;
        max-width: 360px;
        min-height: 120px;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(139, 92, 246, 0.1);
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border);
    }

    .card-header i {
        font-size: 1.4rem;
        color: var(--accent);
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .value {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0.3rem 0;
        color: var(--accent);
        line-height: 1;
    }

    .unit {
        font-size: 0.85rem;
        color: #a78bfa;
        margin-top: auto;
        opacity: 0.8;
    }

    .list {
        list-style: none;
        padding: 0;
        margin: 0;
        flex: 1;
        max-height: 200px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--accent) var(--border);
    }

    .list::-webkit-scrollbar {
        width: 5px;
    }

    .list::-webkit-scrollbar-track {
        background: var(--border);
        border-radius: 2px;
    }

    .list::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 2px;
    }

    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0;
        border-bottom: 1px solid var(--border);
        font-size: 0.9rem;
    }
    .list-item .t{
        width: 200px;
    }

    .list-item:last-child {
        border-bottom: none;
    }

    .progress {
        height: 6px;
        background: var(--border);
        border-radius: 3px;
        overflow: hidden;
        margin: 1rem 0 0.5rem;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent), var(--accent-dark));
        transition: width 0.5s ease;
    }

    .badge {
        display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 500;
    width: 28px;
    background: var(--border);
    color: var(--accent);
    margin-right: 10px;
    }
    #bad{
        width: 40px;
    }
    .badge.stock {
        background: var(--warning);
        color: white;
    }

    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }

        h1 {
            font-size: 1.8rem;
            margin: 1rem 0 2rem;
        }

        .card {
            flex: 1 1 100%;
            max-width: 100%;
            min-height: 100px;
        }

        .value {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 480px) {
        .card-header h3 {
            font-size: 1rem;
        }

        .list-item {
            font-size: 0.85rem;
            padding: 0.5rem 0;
        }

        .badge {
            padding: 0.2rem 0.6rem;
        }
    }

    .preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--bg);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .preloader.active {
        opacity: 1;
        visibility: visible;
    }

    .preloader-content {
        text-align: center;
        max-width: 260px;
        padding: 15px;
    }

    .preloader-text {
        color: var(--accent);
        margin-top: 0.8rem;
        font-size: 1rem;
    }
</style>
</head>
<body>
<div class="preloader" id="preloader">
        <div class="preloader-content">
            <div class="tenor-gif-embed" 
                 data-postid="15026582" 
                 data-share-method="host" 
                 data-aspect-ratio="1" 
                 data-width="100%">
            </div>
            <p class="preloader-text">Загружаем данные...</p>
        </div>
    </div>
    <h1>📊 Полная статистика магазина</h1>
    <div class="grid" id="statsGrid"></div>

    <script>
   const preloader = document.getElementById('preloader');
        let isInitialLoad = true;
        let preloaderTimeout;

        async function fetchStats(initialLoad = false) {
            try {
                if (initialLoad) {
                    preloader.classList.add('active');
                    preloaderTimeout = setTimeout(() => {
                        preloader.classList.remove('active');
                    }, 5000);
                }

                const response = await fetch('ajax/get_stats.php');
                const data = await response.json();
                renderStats(data);

            } catch (error) {
                console.error('Ошибка загрузки:', error);
            } finally {
                if (initialLoad) {
                    clearTimeout(preloaderTimeout);
                    preloader.classList.remove('active');
                    isInitialLoad = false;
                }
            }
        }

        // Первый запуск с прелоадером
        fetchStats(true);
        
        // Последующие обновления без прелоадера
        setInterval(() => fetchStats(false), 5000);
        const format = {
            number: (num) => new Intl.NumberFormat('ru-RU').format(num || 0),
            currency: (num) => new Intl.NumberFormat('ru-RU', { 
                style: 'currency', 
                currency: 'RUB' 
            }).format(num).replace('RUB', '₽'),
            date: (dateStr) => new Date(dateStr).toLocaleString('ru-RU', {
                day: 'numeric',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit'
            })
        };

      

        function renderStats(data) {
            const grid = document.getElementById('statsGrid');
            grid.innerHTML = '';

            // Блок 1: Всего пользователей
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>👥</i>
                        <h3>Всего пользователей</h3>
                    </div>
                    <div class="value">${format.number(data.total_users)}</div>
                    <span class="unit">зарегистрировано</span>
                </div>
            `;

            // Блок 2: Новые пользователи (24ч)
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>👤</i>
                        <h3>Новые пользователи</h3>
                    </div>
                    <div class="value">${format.number(data.new_users_24h)}</div>
                    <span class="unit">за 24 часа</span>
                </div>
            `;

            // Блок 3: Активные пользователи
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🟢</i>
                        <h3>Онлайн сейчас</h3>
                    </div>
                    <div class="value">${format.number(data.active_users)}</div>
                    <span class="unit">активных сессий</span>
                </div>
            `;

            // Блок 4: Топ покупатели (расширенный)
            grid.innerHTML += `
                <div class="card list-card">
                    <div class="card-header">
                        <i>🏆</i>
                        <h3>Топ покупатели</h3>
                    </div>
                    <ul class="list">
                        ${data.top_users.map(user => `
                            <li class="list-item">
                                <span>${user.name}</span>
                                <span>${format.currency(user.total)}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            // Блок 4: Пользователи без заказов
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>📭</i>
                        <h3>Без заказов</h3>
                    </div>
                    <div class="value">${format.number(data.users_no_orders)}</div>
                    <span class="unit">пользователей</span>
                </div>
            `;
            // Блок 5: Товары с низким запасом
            grid.innerHTML += `
                <div class="card">
    <div class="card-header">
        <i>⚠️</i>
        <h3>Низкий запас</h3>
    </div>
    <ul class="list">
        ${data.low_stock.map(item => `
            <li class="list-item">
                <span class="t">${item.name}</span>
                <span class="badge">${item.stock_quantity} шт.</span>
            </li>
        `).join('')}
    </ul>
    <span class="unit">Товары с остатком менее 10</span>
</div>
            `;

            // Блок 6: Средняя цена
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>💰</i>
                        <h3>Средняя цена</h3>
                    </div>
                    <div class="value">${format.currency(data.avg_price)}</div>
                    <span class="unit">по всем товарам</span>
                </div>
            `;

            // Блок 7: Топ-3 скидки
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🎁</i>
                        <h3>Макс. скидки</h3>
                    </div>
                    <ul class="list">
                        ${data.top_discounts.map(item => `
                            <li class="list-item">
                                <span>${item.name}</span>
                                <span class="badge" id="bad">-${item.discount}%</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            // Блок 8: Новые товары
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🆕</i>
                        <h3>Новые поступления</h3>
                    </div>
                    <ul class="list">
                        ${data.new_products.map(product => `
                            <li class="list-item">
                                <span>${product}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            // Блок 9: Средний чек
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🧾</i>
                        <h3>Средний чек</h3>
                    </div>
                    <div class="value">${format.currency(data.avg_order)}</div>
                    <span class="unit">по всем заказам</span>
                </div>
            `;

            // Блок 10: Заказы сегодня
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>📦</i>
                        <h3>Сегодняшние заказы</h3>
                    </div>
                    <div class="value">${format.number(data.today_orders)}</div>
                    <span class="unit">за 24 часа</span>
                </div>
            `;

            // Блок 11: Макс. заказ
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>💎</i>
                        <h3>Макс. заказ</h3>
                    </div>
                    <div class="value">${format.currency(data.max_order)}</div>
                    <span class="unit">самый дорогой</span>
                </div>
            `;

            // Блок 12: Возвраты
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>↩️</i>
                        <h3>Возвраты</h3>
                    </div>
                    <div class="value">${format.number(data.returns)}</div>
                    <span class="unit">в обработке</span>
                </div>
            `;

            // Блок 13: Выручка за месяц
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>📆</i>
                        <h3>Выручка за месяц</h3>
                    </div>
                    <div class="value">${format.currency(data.month_revenue)}</div>
                    <div class="progress">
                        <div class="progress-fill" style="width: 100%"></div>
                    </div>
                </div>
            `;

            // Блок 14: Топ товары
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🚀</i>
                        <h3>Топ продажи</h3>
                    </div>
                    <ul class="list">
                        ${data.top_products.map(item => `
                            <li class="list-item">
                                <span>${item.name}</span>
                                <span>${format.currency(item.revenue)}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            // Блок 15: Сумма скидок
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🎫</i>
                        <h3>Сумма скидок</h3>
                    </div>
                    <div class="value">${format.currency(data.total_discounts)}</div>
                    <span class="unit">всего сэкономили</span>
                </div>
            `;

            // Блок 16: Неоплаченные заказы
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>⏳</i>
                        <h3>Неоплаченные</h3>
                    </div>
                    <div class="value">${format.number(data.unpaid_orders)}</div>
                    <span class="unit">новые заказы</span>
                </div>
            `;

            // Блок 17: Топ избранное
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>❤️</i>
                        <h3>Популярное</h3>
                    </div>
                    <ul class="list">
                        ${data.top_favorites.map(item => `
                            <li class="list-item">
                                <span>${item.name}</span>
                                <span>${item.fav_count} ❤️</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            // Блок 18: Сравнения
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>⚖️</i>
                        <h3>Сравнения</h3>
                    </div>
                    <div class="value">${format.number(data.comparisons)}</div>
                    <span class="unit">активных списков</span>
                </div>
            `;

            // Блок 19: Корзины
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🛒</i>
                        <h3>Корзины</h3>
                    </div>
                    <div class="value">${format.number(data.active_carts)}</div>
                    <span class="unit">с товарами</span>
                </div>
            `;

            // Блок 20: Логи
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>📝</i>
                        <h3>Последние действия</h3>
                    </div>
                    <ul class="list">
                        ${data.recent_logs.map(log => `
                            <li class="list-item">
                                <span>${log.action_description}</span>
                                <span class="unit">${format.date(log.created_at)}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            // Блок 21: Активные промо
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🎉</i>
                        <h3>Активные акции</h3>
                    </div>
                    <ul class="list">
                        ${data.active_promos.map(promo => `
                            <li class="list-item">
                                <span>${promo}</span>
                                <span class="badge" id="bad">Активно</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            // Блок 22: Топ рейтинг
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>⭐</i>
                        <h3>Лучшие оценки</h3>
                    </div>
                    <ul class="list">
                        ${data.top_rated.map(item => `
                            <li class="list-item">
                                <span>${item.name}</span>
                                <span>${item.avg_rating.toFixed(1)}/5</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            // Блок 23: Отзывы на модерации
            grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>🕵️</i>
                        <h3>Отзывы</h3>
                    </div>
                    <div class="value">${format.number(data.pending_reviews)}</div>
                    <span class="unit">на проверке</span>
                </div>
            `;
// Блок 24: Конверсия
grid.innerHTML += `
                <div class="card">
                    <div class="card-header">
                        <i>📈</i>
                        <h3>Конверсия</h3>
                    </div>
                    <div class="value">${(data.conversion || 0).toFixed(1)}%</div>
                    <div class="progress">
                        <div class="progress-fill" 
                             style="width: ${Math.min(data.conversion || 0, 100)}%">
                        </div>
                    </div>
                </div>
            `;
        }

        setInterval(fetchStats, 5000);
        // clearInterval(updateInterval);
        fetchStats();
    </script>
     <script type="text/javascript" async src="https://tenor.com/embed.js"></script>
</body>
</html>