<?php
/**
 * API: Подать заявку на выдачу книги
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не поддерживается']);
    exit;
}

requireCsrfToken();

$currentUser = getCurrentUser();

if ($currentUser['role'] !== ROLE_READER) {
    http_response_code(403);
    echo json_encode(['error' => 'Только читатели могут подавать заявки']);
    exit;
}

$bookId = intval($_POST['book_id'] ?? 0);

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверный ID книги']);
    exit;
}

$db = getDB();

// Проверить существование книги и ее доступность
$book = $db->fetchOne("SELECT * FROM books WHERE id = ?", [$bookId]);

if (!$book) {
    http_response_code(404);
    echo json_encode(['error' => 'Книга не найдена']);
    exit;
}

if ($book['status'] !== BOOK_STATUS_AVAILABLE) {
    http_response_code(400);
    echo json_encode(['error' => 'Книга недоступна']);
    exit;
}

// Проверить наличие активной заявки
$existingRequest = $db->fetchOne(
    "SELECT id FROM requests WHERE book_id = ? AND reader_id = ? AND status = ?",
    [$bookId, $currentUser['id'], REQUEST_STATUS_PENDING]
);

if ($existingRequest) {
    http_response_code(400);
    echo json_encode(['error' => 'Заявка уже отправлена']);
    exit;
}

// Создать заявку
$sql = "INSERT INTO requests (book_id, reader_id, status) VALUES (?, ?, ?)";
$result = $db->execute($sql, [$bookId, $currentUser['id'], REQUEST_STATUS_PENDING]);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена!'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка при создании заявки']);
}
?>
