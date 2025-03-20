<?php
session_start();
if ($_SESSION['role'] != '1') {
    header("Location: login.php"); // Если не админ, перенаправить на главную
    exit();
}
// Контент админ панели
echo "Добро пожаловать, администратор!";
?>
