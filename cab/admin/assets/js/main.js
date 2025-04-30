// Функции для модальных окон
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Закрытие модального окна при клике вне его
window.onclick = function(event) {
    const modals = document.getElementsByClassName('modal');
    for (let modal of modals) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
};

// Динамическая загрузка компонентов
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar-link[data-section]');
    const contentSection = document.getElementById('active-section');

    // Показываем дашборд по умолчанию
    sidebarLinks.forEach(link => {
        if (link.getAttribute('data-section') === 'dashboard') {
            link.classList.add('active');
        }
    });

    // Обработчик клика по пунктам меню
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            if (!section) return; // Игнорируем ссылку "Выйти"

            // Удаляем активный класс у всех ссылок
            sidebarLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            // Загружаем компонент через fetch
            fetch(`/brow12/cab/admin/components/${section}.php`)
                .then(response => response.text())
                .then(html => {
                    // Анимация: сначала скрываем текущий контент
                    contentSection.classList.remove('active');
                    setTimeout(() => {
                        // Обновляем содержимое
                        contentSection.innerHTML = html;
                        // Показываем с анимацией
                        contentSection.classList.add('active');

                        // Повторно инициализируем скрипты внутри компонента
                        const scripts = contentSection.getElementsByTagName('script');
                        for (let script of scripts) {
                            const newScript = document.createElement('script');
                            newScript.text = script.text;
                            document.body.appendChild(newScript);
                        }
                    }, 500); // Задержка для анимации
                })
                .catch(error => {
                    console.error('Ошибка загрузки компонента:', error);
                    contentSection.innerHTML = '<p>Ошибка загрузки раздела.</p>';
                });
        });
    });
});