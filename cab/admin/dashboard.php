<h2>Дашборд</h2>
<div class="dashboard">
    <div class="card">
        <h3>Общая статистика</h3>
        <p>Всего заказов: <span id="total-orders">Загрузка...</span></p>
        <p>Всего товаров: <span id="total-products">Загрузка...</span></p>
        <p>Всего пользователей: <span id="total-users">Загрузка...</span></p>
        <p>Новых заказов за сегодня: <span id="new-orders-today">Загрузка...</span></p>
    </div>
    <div class="card">
        <h3>Статус заказов</h3>
        <canvas id="order-status-chart"></canvas>
    </div>
    <div class="card">
        <h3>Популярные товары</h3>
        <canvas id="popular-products-chart"></canvas>
    </div>
</div>

<style>
    .dashboard {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    .card {
        background: #2a2a3e;
        padding: 20px;
        border-radius: 8px;
    }
    .card h3 {
        margin-top: 0;
    }
    canvas {
        max-width: 100%;
    }
</style>