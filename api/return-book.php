<?php
/**
 * API: Принять книгу от читателя (администратор)
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

$loanId = intval($_POST['loan_id'] ?? 0);
$bookId = intval($_POST['book_id'] ?? 0);

if ($loanId <= 0 || $bookId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверные параметры']);
    exit;
}

$db = getDB();
$db->beginTransaction();

try {
    // Проверить существование выдачи
    $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ? AND book_id = ?",
        [$loanId, $bookId]);

    if (!$loan) {
        throw new Exception('Выдача не найдена');
    }

    // Обновить статус выдачи
    $sql = "UPDATE loans
            SET status = ?, actual_return_date = CURDATE()
            WHERE id = ?";

    $db->execute($sql, [LOAN_STATUS_RETURNED, $loanId]);

    // Обновить статус книги
    $db->execute("UPDATE books SET status = ? WHERE id = ?",
        [BOOK_STATUS_AVAILABLE, $bookId]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Книга успешно принята!'
    ]);

} catch (Exception $e) {
    $db->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
