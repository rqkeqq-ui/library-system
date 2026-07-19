<?php
/**
 * Вспомогательные функции
 */

require_once 'config.php';
require_once 'db.php';

/**
 * Проверка авторизации пользователя
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Получить текущего пользователя
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $db = getDB();
    $sql = "SELECT id, email, role, full_name, ticket_number FROM users WHERE id = ?";
    return $db->fetchOne($sql, [$_SESSION['user_id']]);
}

/**
 * Проверка роли администратора
 */
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

/**
 * Проверка роли читателя
 */
function isReader() {
    if (!isLoggedIn()) {
        return false;
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_READER;
}

/**
 * Перенаправление на страницу
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Получить или создать токен защиты от CSRF-атак.
 */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Проверить CSRF-токен, переданный формой или API-запросом.
 */
function isCsrfTokenValid($token) {
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Завершить API-запрос с понятной ошибкой, если CSRF-токен неверный.
 */
function requireCsrfToken() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

    if (!isCsrfTokenValid($token)) {
        http_response_code(419);
        echo json_encode(['error' => 'Сессия устарела. Обновите страницу и повторите действие.']);
        exit;
    }
}

/**
 * Защита от несанкционированного доступа
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Защита страниц администратора
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php');
    }
}

/**
 * Экранирование HTML
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Форматирование даты
 */
function formatDate($date, $format = 'd.m.Y') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Расчет количества просроченных дней
 */
function calculateOverdueDays($returnDate) {
    $today = new DateTime();
    $return = new DateTime($returnDate);

    if ($today <= $return) {
        return 0;
    }

    $diff = $today->diff($return);
    return $diff->days;
}

/**
 * Получить количество просроченных книг для читателя
 */
function getOverdueCount($userId) {
    $db = getDB();
    $sql = "SELECT COUNT(*) as count FROM loans
            WHERE reader_id = ? AND status = ? AND return_date < CURDATE()";
    $result = $db->fetchOne($sql, [$userId, LOAN_STATUS_ACTIVE]);
    return $result ? $result['count'] : 0;
}

/**
 * Обновить статусы просроченных выдач
 */
function updateOverdueLoans() {
    $db = getDB();
    $sql = "UPDATE loans SET status = ?
            WHERE status = ? AND return_date < CURDATE()";
    return $db->execute($sql, [LOAN_STATUS_OVERDUE, LOAN_STATUS_ACTIVE]);
}

/**
 * Получить инициалы пользователя
 */
function getInitials($fullName) {
    $parts = explode(' ', trim($fullName));
    if (count($parts) >= 2) {
        return mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1);
    }
    return mb_substr($fullName, 0, 2);
}

/**
 * Генерация случайного номера билета
 */
function generateTicketNumber() {
    return 'R' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * Хеширование пароля
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Проверка пароля
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Валидация email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Установить flash сообщение
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Получить и удалить flash сообщение
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Получить статистику для администратора
 */
function getLibraryStatistics() {
    $db = getDB();

    // Всего книг
    $totalBooks = $db->fetchOne("SELECT COUNT(*) as count FROM books");

    // Выдано книг
    $issuedBooks = $db->fetchOne("SELECT COUNT(*) as count FROM books WHERE status = ?", [BOOK_STATUS_ISSUED]);

    // Просрочено
    $overdueBooks = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = ? OR (status = ? AND return_date < CURDATE())",
        [LOAN_STATUS_OVERDUE, LOAN_STATUS_ACTIVE]);

    return [
        'total' => $totalBooks['count'] ?? 0,
        'issued' => $issuedBooks['count'] ?? 0,
        'overdue' => $overdueBooks['count'] ?? 0
    ];
}

/**
 * Получить иконку книги по жанру
 */
function getBookIcon($genre) {
    $genre = mb_strtolower(trim($genre));

    $icons = [
        'антиутопия' => '📕',
        'классика' => '📗',
        'фэнтези' => '📘',
        'фантастика' => '🚀',
        'проза' => '📙',
        'философия' => '💭',
        'детектив' => '🔍',
        'триллер' => '😱',
        'роман' => '💕',
        'поэзия' => '✨',
        'научная' => '🔬',
        'история' => '📜',
        'биография' => '👤',
        'приключения' => '🗺️',
        'ужасы' => '👻',
        'комедия' => '😄',
        'драма' => '🎭',
    ];

    foreach ($icons as $key => $icon) {
        if (strpos($genre, $key) !== false) {
            return $icon;
        }
    }

    return '📚'; // По умолчанию
}

/**
 * Поиск книг
 */
function searchBooks($query, $filters = []) {
    $db = getDB();

    $sql = "SELECT * FROM books WHERE 1=1";
    $params = [];

    // Быстрый поиск
    if (!empty($query)) {
        $sql .= " AND (title LIKE ? OR author LIKE ? OR genre LIKE ?)";
        $searchTerm = "%$query%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Расширенный поиск
    if (!empty($filters['title'])) {
        $sql .= " AND title LIKE ?";
        $params[] = "%" . $filters['title'] . "%";
    }

    if (!empty($filters['author'])) {
        $sql .= " AND author LIKE ?";
        $params[] = "%" . $filters['author'] . "%";
    }

    if (!empty($filters['isbn'])) {
        $sql .= " AND isbn = ?";
        $params[] = $filters['isbn'];
    }

    if (!empty($filters['genre'])) {
        $sql .= " AND genre LIKE ?";
        $params[] = "%" . $filters['genre'] . "%";
    }

    $sql .= " ORDER BY title ASC";

    return $db->query($sql, $params);
}
?>
