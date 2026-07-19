<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Обновить просроченные выдачи
updateOverdueLoans();

// Если уже авторизован, перенаправить
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isCsrfTokenValid($_POST['csrf_token'] ?? null)) {
        $error = 'Сессия формы устарела. Обновите страницу и повторите вход.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Пожалуйста, заполните все поля';
        } else {
            $db = getDB();
            $sql = "SELECT * FROM users WHERE email = ?";
            $user = $db->fetchOne($sql, [$email]);

            if ($user && verifyPassword($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];

                setFlashMessage('success', 'Добро пожаловать, ' . $user['full_name'] . '!');
                redirect('index.php');
            } else {
                $error = 'Неверный email или пароль';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <h1><?= SITE_NAME ?></h1>
                <p><?= SITE_SUBTITLE ?></p>
            </div>

            <h2 class="auth-title">Вход в систему</h2>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@mail.ru"
                        value="<?= escape($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span>Запомнить меня</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Войти
                </button>
            </form>

            <div class="auth-footer">
                <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
            </div>

        </div>
    </div>
</body>
</html>
