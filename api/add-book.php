<?php
/**
 * API: Добавить новую книгу (только администратор)
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не поддерживается']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$genre = trim($_POST['genre'] ?? '');
$isbn = trim($_POST['isbn'] ?? '');
$description = trim($_POST['description'] ?? '');

// Валидация
$errors = [];

if (empty($title)) $errors[] = 'Введите название книги';
if (empty($author)) $errors[] = 'Введите автора';
if (empty($genre)) $errors[] = 'Введите жанр';
if (empty($isbn)) $errors[] = 'Введите ISBN';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

$db = getDB();

// Проверить уникальность ISBN
$existing = $db->fetchOne("SELECT id FROM books WHERE isbn = ?", [$isbn]);
if ($existing) {
    http_response_code(400);
    echo json_encode(['error' => 'Книга с таким ISBN уже существует']);
    exit;
}

// Добавить книгу
$sql = "INSERT INTO books (title, author, genre, isbn, description, status)
        VALUES (?, ?, ?, ?, ?, ?)";

$result = $db->execute($sql, [
    $title,
    $author,
    $genre,
    $isbn,
    $description,
    BOOK_STATUS_AVAILABLE
]);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Книга успешно добавлена!',
        'book_id' => $db->lastInsertId()
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка при добавлении книги']);
}
?>
