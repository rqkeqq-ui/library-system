<?php
/**
 * API: Получить доступные книги для выдачи
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

requireAdmin();

$db = getDB();
$books = $db->query("SELECT * FROM books WHERE status = ? ORDER BY title ASC",
    [BOOK_STATUS_AVAILABLE]);

echo json_encode([
    'success' => true,
    'books' => $books
]);
?>
