<?php
/**
 * Конфигурация базы данных и основные настройки
 */

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_system');
define('DB_USER', 'root');
define('DB_PASS', '');
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

// Начало сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
