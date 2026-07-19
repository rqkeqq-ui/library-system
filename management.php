<?php
define('PAGE_TITLE', 'Управление читателями');
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireAdmin();
updateOverdueLoans();

$db = getDB();

// Получить статистику
$stats = getLibraryStatistics();

// Получить всех читателей с их активными книгами
$sql = "SELECT u.id, u.full_name, u.ticket_number, u.email
        FROM users u
        WHERE u.role = ?
        ORDER BY u.full_name ASC";

$readers = $db->query($sql, [ROLE_READER]);

// Для каждого читателя получить активные книги
foreach ($readers as &$reader) {
    $loansSql = "SELECT l.*, b.title, b.author
                 FROM loans l
                 JOIN books b ON l.book_id = b.id
                 WHERE l.reader_id = ? AND l.status IN (?, ?)
                 ORDER BY l.return_date ASC";

    $reader['active_loans'] = $db->query($loansSql, [
        $reader['id'],
        LOAN_STATUS_ACTIVE,
        LOAN_STATUS_OVERDUE
    ]);
}

// Получить pending заявки
$pendingRequests = $db->query(
    "SELECT r.*, b.title, b.author, u.full_name
     FROM requests r
     JOIN books b ON r.book_id = b.id
     JOIN users u ON r.reader_id = u.id
     WHERE r.status = ?
     ORDER BY r.created_at ASC",
    [REQUEST_STATUS_PENDING]
);

include 'includes/header.php';
?>

<div class="page-header">
    <h2>⚙️ Управление</h2>
</div>

<!-- Статистика -->
<div class="statistics-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-label">Всего книг</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['issued'] ?></div>
        <div class="stat-label">Выдано</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['overdue'] ?></div>
        <div class="stat-label">Просрочено</div>
    </div>
</div>

<!-- Заявки на выдачу -->
<?php if (!empty($pendingRequests)): ?>
<div class="section">
    <h3>📋 Новые заявки (<?= count($pendingRequests) ?>)</h3>
    <div class="requests-list">
        <?php foreach ($pendingRequests as $request): ?>
            <div class="request-card">
                <div class="request-info">
                    <strong><?= escape($request['full_name']) ?></strong> хочет взять
                    <strong>«<?= escape($request['title']) ?>»</strong>
                </div>
                <div class="request-actions">
                    <button class="btn btn-sm btn-success" onclick="approveRequest(<?= $request['id'] ?>, <?= $request['reader_id'] ?>, <?= $request['book_id'] ?>)">
                        ✓ Одобрить
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="rejectRequest(<?= $request['id'] ?>)">
                        ✗ Отклонить
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Управление читателями -->
<div class="section">
    <h3>👥 Управление читателями</h3>
    <p class="subtitle">Выдавайте, принимайте и продлевайте книги</p>

    <div class="readers-list">
        <?php foreach ($readers as $reader): ?>
            <div class="reader-card">
                <div class="reader-header">
                    <div class="reader-avatar">
                        <span class="avatar-circle reader">
                            <?= getInitials($reader['full_name']) ?>
                        </span>
                    </div>
                    <div class="reader-info">
                        <h4><?= escape($reader['full_name']) ?></h4>
                        <p class="reader-ticket">Билет №<?= escape($reader['ticket_number']) ?></p>
                    </div>
                    <?php if (empty($reader['active_loans'])): ?>
                        <button class="btn btn-sm btn-primary" onclick='openIssueModal(<?= (int) $reader['id'] ?>, <?= escape(json_encode($reader['full_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>)'>
                            📋 Выдать
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (!empty($reader['active_loans'])): ?>
                    <div class="active-books-section">
                        <h5>АКТИВНЫЕ КНИГИ</h5>
                        <?php foreach ($reader['active_loans'] as $loan): ?>
                            <?php $overdueDays = calculateOverdueDays($loan['return_date']); ?>
                            <div class="active-book-item <?= $overdueDays > 0 ? 'overdue' : '' ?>">
                                <div class="book-info-compact">
                                    <span class="book-icon">📚</span>
                                    <div>
                                        <strong><?= escape($loan['title']) ?></strong><br>
                                        <small>
                                            до <?= formatDate($loan['return_date']) ?>
                                            <?php if ($overdueDays > 0): ?>
                                                <span class="text-danger"> • Просрочено <?= $overdueDays ?> дн.</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="loan-actions">
                                    <button
                                        class="btn-icon"
                                        onclick='openExtendModal(<?= (int) $loan['id'] ?>, <?= (int) $loan['book_id'] ?>, <?= escape(json_encode($loan['title'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>, <?= escape(json_encode($reader['full_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>)'
                                        title="Продлить">
                                        ⏰
                                    </button>
                                    <button
                                        class="btn-icon btn-success"
                                        onclick='openReturnModal(<?= (int) $loan['id'] ?>, <?= (int) $loan['book_id'] ?>, <?= escape(json_encode($loan['title'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>, <?= escape(json_encode($reader['full_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>)'
                                        title="Принять">
                                        ✓
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-books">Нет активных книг</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Модальные окна -->
<?php include 'includes/management-modals.php'; ?>

<script src="js/management.js?v=20260719"></script>

<?php include 'includes/footer.php'; ?>
