<form id="registerForm">
    <input type="text" id="username" name="username" placeholder="Username" required>
    <input type="email" id="email" name="email" placeholder="Email" required>
    <input type="password" id="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Сбор данных формы
    const formData = new FormData();
    formData.append('username', document.getElementById('username').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('password', document.getElementById('password').value);

    // Создание XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/register.php', true);

    xhr.onload = function() {
        const response = JSON.parse(xhr.responseText);
        alert(response.message);
        if (response.success) {
            window.location.href = "login.php"; // Перенаправление на страницу авторизации
        }
    };

    xhr.send(formData); // Отправка формы через POST
});
</script>