<?php
define('PAGE_TITLE', 'Мои книги');
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();
updateOverdueLoans();

$currentUser = getCurrentUser();
$db = getDB();

// Получить активные выдачи
$sql = "SELECT l.*, b.title, b.author, b.genre
        FROM loans l
        JOIN books b ON l.book_id = b.id
        WHERE l.reader_id = ? AND l.status IN (?, ?)
        ORDER BY l.return_date ASC";

$myBooks = $db->query($sql, [
    $currentUser['id'],
    LOAN_STATUS_ACTIVE,
    LOAN_STATUS_OVERDUE
]);

include 'includes/header.php';
?>

<div class="page-header">
    <h2>📖 Мои книги</h2>
</div>

<?php if (empty($myBooks)): ?>
    <div class="empty-state">
        <p>📚 У вас нет взятых книг</p>
        <a href="index.php" class="btn btn-primary">Перейти в каталог</a>
    </div>
<?php else: ?>
    <div class="my-books-list">
        <?php foreach ($myBooks as $loan): ?>
            <?php
            $overdueDays = calculateOverdueDays($loan['return_date']);
            $isOverdue = $overdueDays > 0;
            ?>
            <div class="my-book-card <?= $isOverdue ? 'overdue' : '' ?>">
                <div class="book-icon">
                    <?= getBookIcon($loan['genre']) ?>
                </div>

                <div class="book-details">
                    <h3 class="book-title"><?= escape($loan['title']) ?></h3>
                    <p class="book-author"><?= escape($loan['author']) ?></p>

                    <div class="loan-dates">
                        <span class="loan-date">
                            📅 Взята: <?= formatDate($loan['issue_date']) ?>
                        </span>
                        <span class="return-date">
                            🔄 Вернуть до: <?= formatDate($loan['return_date']) ?>
                        </span>
                    </div>

                    <div class="loan-status">
                        <?php if ($isOverdue): ?>
                            <span class="badge badge-danger">
                                ⏰ Просрочено <?= $overdueDays ?> дн.
                            </span>
                        <?php else: ?>
                            <span class="badge badge-success">
                                ✅ В срок
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
