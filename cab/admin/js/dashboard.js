document.addEventListener('DOMContentLoaded', () => {
    // Загрузка общей статистики
    fetch('./api/index.php?action=analytics&table=Orders&mode=analytics', { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.message) return;
            const totalOrders = data.reduce((sum, item) => sum + parseInt(item.count), 0);
            document.getElementById('total-orders').textContent = totalOrders;

            const ctx = document.getElementById('order-status-chart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.map(item => item.order_status),
                    datasets: [{
                        data: data.map(item => item.count),
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        })
        .catch(error => console.error('Ошибка загрузки данных о заказах:', error));

    fetch('./api/index.php?action=analytics&table=Products', { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.message) return;
            document.getElementById('total-products').textContent = data.length;

            const ctx = document.getElementById('popular-products-chart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.slice(0, 5).map(item => item.name),
                    datasets: [{
                        label: 'Количество заказов',
                        data: data.slice(0, 5).map(item => item.orders || 0),
                        backgroundColor: '#36A2EB'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })
        .catch(error => console.error('Ошибка загрузки данных о товарах:', error));

    fetch('./api/index.php?action=analytics&table=Users&mode=analytics', { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.message) return;
            const totalUsers = data.reduce((sum, item) => sum + parseInt(item.count), 0);
            document.getElementById('total-users').textContent = totalUsers;
        })
        .catch(error => console.error('Ошибка загрузки данных о пользователях:', error));

    fetch('./api/index.php?action=analytics&table=Orders', { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.message) return;
            const today = new Date().toISOString().split('T')[0];
            const newOrdersToday = data.filter(order => order.created_at.split(' ')[0] === today).length;
            document.getElementById('new-orders-today').textContent = newOrdersToday;
        })
        .catch(error => console.error('Ошибка загрузки новых заказов:', error));
});