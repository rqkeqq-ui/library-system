<?php
require_once 'includes/config.php';

// Уничтожить все данные сессии
session_unset();
session_destroy();

// Перенаправить на страницу входа
header("Location: login.php");
exit;
?>
