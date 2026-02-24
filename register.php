<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Если уже авторизован, перенаправить
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Валидация
    if (empty($full_name)) {
        $errors[] = 'Введите полное имя';
    }

    if (empty($email)) {
        $errors[] = 'Введите email';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Неверный формат email';
    }

    if (empty($password)) {
        $errors[] = 'Введите пароль';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать минимум 6 символов';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }

    // Проверка существования email
    if (empty($errors)) {
        $db = getDB();
        $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existingUser) {
            $errors[] = 'Пользователь с таким email уже существует';
        }
    }

    // Регистрация
    if (empty($errors)) {
        $password_hash = hashPassword($password);
        $ticket_number = generateTicketNumber();

        $sql = "INSERT INTO users (email, password_hash, role, full_name, ticket_number)
                VALUES (?, ?, ?, ?, ?)";

        if ($db->execute($sql, [$email, $password_hash, ROLE_READER, $full_name, $ticket_number])) {
            $success = true;
            setFlashMessage('success', 'Регистрация успешна! Теперь вы можете войти.');
        } else {
            $errors[] = 'Ошибка при регистрации. Попробуйте позже.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <h1><?= SITE_NAME ?></h1>
                <p><?= SITE_SUBTITLE ?></p>
            </div>

            <h2 class="auth-title">Регистрация читателя</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Регистрация успешна! <a href="login.php">Войти</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" action="register.php" class="auth-form">
                    <div class="form-group">
                        <label for="full_name">Полное имя</label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            placeholder="Иванов Иван Иванович"
                            value="<?= escape($_POST['full_name'] ?? '') ?>"
                            required
                            autocomplete="name"
                        >
                    </div>

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
                            autocomplete="new-password"
                            minlength="6"
                        >
                        <small>Минимум 6 символов</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Повтор пароля</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            minlength="6"
                        >
                    </div>

                    <div class="info-box">
                        ℹ️ Номер билета будет присвоен автоматически
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Зарегистрироваться
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
            </div>
        </div>
    </div>
</body>
</html>
