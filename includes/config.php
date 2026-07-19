<?php
/**
 * Конфигурация базы данных и основные настройки
 */

/**
 * Загружает переменные окружения из локального файла .env.
 * Файл не хранится в репозитории: используйте .env.example как шаблон.
 */
function loadEnvFile($path) {
    if (!is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function envValue($key, $default = '') {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

loadEnvFile(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

// Настройки базы данных
define('DB_HOST', envValue('DB_HOST', 'localhost'));
define('DB_NAME', envValue('DB_NAME', 'library_system'));
define('DB_USER', envValue('DB_USER'));
define('DB_PASS', envValue('DB_PASS'));
define('DB_CHARSET', 'utf8mb4');

// Настройки сессии
define('SESSION_LIFETIME', 3600 * 24); // 24 часа

// Настройки приложения
define('SITE_NAME', 'Библиотека');
define('SITE_SUBTITLE', 'Система управления');
define('DEFAULT_LOAN_DAYS', 14); // Срок выдачи книги по умолчанию (дней)

// Роли пользователей
define('ROLE_READER', 'reader');
define('ROLE_ADMIN', 'admin');

// Статусы книг
define('BOOK_STATUS_AVAILABLE', 'available');
define('BOOK_STATUS_ISSUED', 'issued');

// Статусы выдач
define('LOAN_STATUS_ACTIVE', 'active');
define('LOAN_STATUS_RETURNED', 'returned');
define('LOAN_STATUS_OVERDUE', 'overdue');

// Статусы заявок
define('REQUEST_STATUS_PENDING', 'pending');
define('REQUEST_STATUS_APPROVED', 'approved');
define('REQUEST_STATUS_REJECTED', 'rejected');

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Безопасные параметры сессии
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
?>
