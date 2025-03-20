<?php
session_start();
if ($_SESSION['role'] != '3') {
    header("Location: login.php"); // Если не курьер, перенаправить на главную
    exit();
}
// Контент кабинета курьера
echo "Добро пожаловать в кабинет курьера!";
?>
