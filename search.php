<?php
define('PAGE_TITLE', 'Расширенный поиск');
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$results = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = [
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'isbn' => trim($_POST['isbn'] ?? ''),
        'genre' => trim($_POST['genre'] ?? '')
    ];

    $results = searchBooks('', $filters);
    $searched = true;
}

include 'includes/header.php';
?>

<div class="page-header">
    <h2>🔍 Расширенный поиск</h2>
    <p class="subtitle">Найдите нужную книгу по различным критериям</p>
</div>

<div class="search-container">
    <form method="POST" action="search.php" class="search-advanced-form">
        <div class="form-group">
            <label for="title">Название книги</label>
            <input
                type="text"
                id="title"
                name="title"
                placeholder="Например: 1984"
                value="<?= escape($_POST['title'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label for="author">Автор</label>
            <input
                type="text"
                id="author"
                name="author"
                placeholder="Например: Оруэлл"
                value="<?= escape($_POST['author'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label for="isbn">ISBN</label>
            <input
                type="text"
                id="isbn"
                name="isbn"
                placeholder="978-5-17-098825-6"
                value="<?= escape($_POST['isbn'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label for="genre">Жанр</label>
            <input
                type="text"
                id="genre"
                name="genre"
                placeholder="Например: Фэнтези"
                value="<?= escape($_POST['genre'] ?? '') ?>"
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            🔍 Найти книги
        </button>
    </form>
</div>

<?php if ($searched): ?>
    <div class="search-results">
        <h3>Результаты поиска (<?= count($results) ?>)</h3>

        <?php if (empty($results)): ?>
            <div class="empty-state">
                <p>📚 Книги не найдены. Попробуйте изменить критерии поиска.</p>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($results as $book): ?>
                    <button type="button" class="book-card" onclick="openBookModal(<?= (int) $book['id'] ?>)" aria-haspopup="dialog">
                        <div class="book-cover">
                            <div class="book-cover-placeholder"><?= getBookIcon($book['genre']) ?></div>
                        </div>
                        <div class="book-info">
                            <h3 class="book-title"><?= escape($book['title']) ?></h3>
                            <p class="book-author"><?= escape($book['author']) ?></p>
                            <p class="book-genre"><?= escape($book['genre']) ?></p>
                            <span class="book-status badge <?= $book['status'] === BOOK_STATUS_AVAILABLE ? 'badge-success' : 'badge-gray' ?>">
                                <?= $book['status'] === BOOK_STATUS_AVAILABLE ? 'Доступна' : 'Выдана' ?>
                            </span>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Модальное окно информации о книге -->
<div id="bookModal" class="modal" role="dialog" aria-modal="true" aria-label="Информация о книге">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal('bookModal')" aria-label="Закрыть окно">&times;</button>
        <div id="bookModalContent"></div>
    </div>
</div>

<script src="js/books.js?v=20260719"></script>

<?php include 'includes/footer.php'; ?>
