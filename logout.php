<?php
require_once 'includes/config.php';

// Уничтожить все данные сессии и cookie браузера
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// Перенаправить на страницу входа
header("Location: login.php");
exit;
?>
