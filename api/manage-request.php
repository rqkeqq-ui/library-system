<?php
/**
 * API: Одобрить или отклонить заявку
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

$requestId = intval($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? ''; // approve или reject

if ($requestId <= 0 || !in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверные параметры']);
    exit;
}

$db = getDB();
$currentUser = getCurrentUser();

if ($action === 'approve') {
    // Получить информацию о заявке
    $request = $db->fetchOne("SELECT * FROM requests WHERE id = ?", [$requestId]);
    if (!$request) {
        http_response_code(404);
        echo json_encode(['error' => 'Заявка не найдена']);
        exit;
    }

    // Проверить доступность книги
    $book = $db->fetchOne("SELECT * FROM books WHERE id = ? AND status = ?",
        [$request['book_id'], BOOK_STATUS_AVAILABLE]);

    if (!$book) {
        http_response_code(400);
        echo json_encode(['error' => 'Книга недоступна']);
        exit;
    }

    $db->beginTransaction();
    try {
        // Создать выдачу
        $issueDate = date('Y-m-d');
        $returnDate = date('Y-m-d', strtotime('+' . DEFAULT_LOAN_DAYS . ' days'));

        $db->execute(
            "INSERT INTO loans (book_id, reader_id, issue_date, return_date, status) VALUES (?, ?, ?, ?, ?)",
            [$request['book_id'], $request['reader_id'], $issueDate, $returnDate, LOAN_STATUS_ACTIVE]
        );

        // Обновить статус книги
        $db->execute("UPDATE books SET status = ? WHERE id = ?",
            [BOOK_STATUS_ISSUED, $request['book_id']]);

        // Одобрить заявку
        $db->execute(
            "UPDATE requests SET status = ?, admin_id = ?, processed_at = NOW() WHERE id = ?",
            [REQUEST_STATUS_APPROVED, $currentUser['id'], $requestId]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Заявка одобрена, книга выдана!'
        ]);

    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка при одобрении заявки']);
    }

} else { // reject
    $result = $db->execute(
        "UPDATE requests SET status = ?, admin_id = ?, processed_at = NOW() WHERE id = ?",
        [REQUEST_STATUS_REJECTED, $currentUser['id'], $requestId]
    );

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Заявка отклонена'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка при отклонении заявки']);
    }
}
?>
