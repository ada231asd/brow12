<script>
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'ajax/logout.php', true);
    xhr.onload = function() {
        const response = JSON.parse(xhr.responseText);
        alert(response.message);
        if (response.success) {
            window.location.href = "index.php"; // Перенаправление на главную страницу
        }
    };
    xhr.send();
</script>