<?php
/**
 * API: Продлить срок выдачи книги (администратор)
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

requireCsrfToken();

$loanId = intval($_POST['loan_id'] ?? 0);
$days = intval($_POST['days'] ?? 0);

if ($loanId <= 0 || $days <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверные параметры']);
    exit;
}

$db = getDB();

// Получить текущую выдачу
$loan = $db->fetchOne("SELECT * FROM loans WHERE id = ? AND status IN (?, ?)",
    [$loanId, LOAN_STATUS_ACTIVE, LOAN_STATUS_OVERDUE]);

if (!$loan) {
    http_response_code(404);
    echo json_encode(['error' => 'Выдача не найдена']);
    exit;
}

// Продлить срок
$newReturnDate = date('Y-m-d', strtotime($loan['return_date'] . ' +' . $days . ' days'));
$newExtendedCount = $loan['extended_count'] + 1;

$sql = "UPDATE loans
        SET return_date = ?, extended_count = ?, status = ?
        WHERE id = ?";

$result = $db->execute($sql, [$newReturnDate, $newExtendedCount, LOAN_STATUS_ACTIVE, $loanId]);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Срок успешно продлен на ' . $days . ' дней!',
        'new_return_date' => formatDate($newReturnDate)
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка при продлении срока']);
}
?>
