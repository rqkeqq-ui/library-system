<?php
/**
 * API: Получить информацию о книге
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

requireLogin();

$bookId = intval($_GET['id'] ?? 0);

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверный ID книги']);
    exit;
}

$db = getDB();
$book = $db->fetchOne("SELECT * FROM books WHERE id = ?", [$bookId]);

if (!$book) {
    http_response_code(404);
    echo json_encode(['error' => 'Книга не найдена']);
    exit;
}

// Проверить, есть ли активная заявка от текущего пользователя
$currentUser = getCurrentUser();
$hasActiveRequest = false;

if ($currentUser['role'] === ROLE_READER) {
    $request = $db->fetchOne(
        "SELECT id FROM requests WHERE book_id = ? AND reader_id = ? AND status = ?",
        [$bookId, $currentUser['id'], REQUEST_STATUS_PENDING]
    );
    $hasActiveRequest = (bool)$request;
}

echo json_encode([
    'success' => true,
    'book' => $book,
    'hasActiveRequest' => $hasActiveRequest,
    'canRequest' => $book['status'] === BOOK_STATUS_AVAILABLE && $currentUser['role'] === ROLE_READER
]);
?>
