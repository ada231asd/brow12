function fetchAnalytics(table, callback) {
    fetch(`./api/index.php?action=analytics&table=${table}`, { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            callback(data);
        })
        .catch(error => alert('Ошибка аналитики: ' + error));
}

function renderChart(canvasId, type, data, label, valueKey) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type,
        data: {
            labels: data.map(item => item.name || item.order_status || item.role),
            datasets: [{
                label,
                data: data.map(item => item[valueKey]),
                backgroundColor: ['#9b59b6', '#8e44ad', '#e74c3c', '#3498db'],
                borderColor: '#e0e0e0',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#e0e0e0' } }
            },
            scales: {
                y: { ticks: { color: '#e0e0e0' }, grid: { color: '#444' } },
                x: { ticks: { color: '#e0e0e0' }, grid: { color: '#444' } }
            }
        }
    });
}