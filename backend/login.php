<form id="loginForm">
    <input type="email" id="email" name="email" placeholder="Email" required>
    <input type="password" id="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Сбор данных формы
    const formData = new FormData();
    formData.append('email', document.getElementById('email').value);
    formData.append('password', document.getElementById('password').value);

    // Создание XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/login.php', true);

    xhr.onload = function() {
        const response = JSON.parse(xhr.responseText);
        alert(response.message);
        if (response.success) {
            window.location.href = "dashboard.php"; // Перенаправление на админ панель или личный кабинет
        }
    };

    xhr.send(formData); // Отправка формы через POST
});
</script>