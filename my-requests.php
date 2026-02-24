<?php
define('PAGE_TITLE', 'Мои заявки');
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$currentUser = getCurrentUser();
$db = getDB();

// Получить все заявки текущего пользователя
$sql = "SELECT r.*, b.title, b.author, b.genre, b.status as book_status
        FROM requests r
        JOIN books b ON r.book_id = b.id
        WHERE r.reader_id = ?
        ORDER BY r.created_at DESC";

$myRequests = $db->query($sql, [$currentUser['id']]);

include 'includes/header.php';
?>

<div class="page-header">
    <h2>📋 Мои заявки</h2>
</div>

<?php if (empty($myRequests)): ?>
    <div class="empty-state">
        <p>📋 У вас нет заявок на книги</p>
        <a href="index.php" class="btn btn-primary">Перейти в каталог</a>
    </div>
<?php else: ?>
    <div class="my-books-list">
        <?php foreach ($myRequests as $request): ?>
            <?php
            $statusClass = '';
            $statusText = '';
            $statusIcon = '';

            switch ($request['status']) {
                case REQUEST_STATUS_PENDING:
                    $statusClass = 'badge-warning';
                    $statusText = 'Ожидает обработки';
                    $statusIcon = '⏳';
                    break;
                case REQUEST_STATUS_APPROVED:
                    $statusClass = 'badge-success';
                    $statusText = 'Одобрена';
                    $statusIcon = '✅';
                    break;
                case REQUEST_STATUS_REJECTED:
                    $statusClass = 'badge-danger';
                    $statusText = 'Отклонена';
                    $statusIcon = '❌';
                    break;
            }
            ?>
            <div class="my-book-card">
                <div class="book-icon">
                    <?= getBookIcon($request['genre']) ?>
                </div>

                <div class="book-details">
                    <h3 class="book-title"><?= escape($request['title']) ?></h3>
                    <p class="book-author"><?= escape($request['author']) ?></p>

                    <div class="loan-dates">
                        <span class="loan-date">
                            📅 Подана: <?= formatDate($request['created_at'], 'd.m.Y H:i') ?>
                        </span>
                        <?php if ($request['processed_at']): ?>
                            <span class="return-date">
                                ✓ Обработана: <?= formatDate($request['processed_at'], 'd.m.Y H:i') ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="loan-status">
                        <span class="badge <?= $statusClass ?>">
                            <?= $statusIcon ?> <?= $statusText ?>
                        </span>
                    </div>

                    <?php if ($request['status'] === REQUEST_STATUS_APPROVED): ?>
                        <div class="info-box" style="margin-top: 8px; font-size: 14px;">
                            ℹ️ Книга была выдана. Проверьте раздел "Мои книги".
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
