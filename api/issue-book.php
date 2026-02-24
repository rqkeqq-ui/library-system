<?php
/**
 * API: Выдать книгу читателю (администратор)
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

$bookId = intval($_POST['book_id'] ?? 0);
$readerId = intval($_POST['reader_id'] ?? 0);
$requestId = intval($_POST['request_id'] ?? 0);

if ($bookId <= 0 || $readerId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверные параметры']);
    exit;
}

$db = getDB();
$db->beginTransaction();

try {
    // Проверить доступность книги
    $book = $db->fetchOne("SELECT * FROM books WHERE id = ? AND status = ?",
        [$bookId, BOOK_STATUS_AVAILABLE]);

    if (!$book) {
        throw new Exception('Книга недоступна');
    }

    // Создать выдачу
    $issueDate = date('Y-m-d');
    $returnDate = date('Y-m-d', strtotime('+' . DEFAULT_LOAN_DAYS . ' days'));

    $sql = "INSERT INTO loans (book_id, reader_id, issue_date, return_date, status)
            VALUES (?, ?, ?, ?, ?)";

    $db->execute($sql, [$bookId, $readerId, $issueDate, $returnDate, LOAN_STATUS_ACTIVE]);

    // Обновить статус книги
    $db->execute("UPDATE books SET status = ? WHERE id = ?",
        [BOOK_STATUS_ISSUED, $bookId]);

    // Если есть связанная заявка, одобрить её
    if ($requestId > 0) {
        $currentUser = getCurrentUser();
        $db->execute(
            "UPDATE requests SET status = ?, admin_id = ?, processed_at = NOW() WHERE id = ?",
            [REQUEST_STATUS_APPROVED, $currentUser['id'], $requestId]
        );
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Книга успешно выдана!'
    ]);

} catch (Exception $e) {
    $db->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
