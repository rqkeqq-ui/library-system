<?php
define('PAGE_TITLE', 'Каталог книг');
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();
updateOverdueLoans();

// Получить все книги
$db = getDB();
$quickSearch = trim($_GET['q'] ?? '');

if ($quickSearch) {
    $books = searchBooks($quickSearch);
} else {
    $books = $db->query("SELECT * FROM books ORDER BY title ASC");
}

include 'includes/header.php';
?>

<div class="page-header">
    <h2>📚 Каталог книг</h2>
    <?php if (isAdmin()): ?>
        <button class="btn btn-primary" onclick="openAddBookModal()">
            ➕ Добавить книгу
        </button>
    <?php endif; ?>
</div>

<!-- Быстрый поиск -->
<div class="quick-search">
    <form action="index.php" method="GET" class="search-form" role="search">
        <input
            type="search"
            name="q"
            placeholder="🔍 Найти книгу, автора или жанр..."
            value="<?= escape($quickSearch) ?>"
            class="search-input"
        >
        <button type="submit" class="btn btn-primary">Найти</button>
        <?php if ($quickSearch): ?>
            <a href="index.php" class="btn btn-secondary">Сбросить</a>
        <?php endif; ?>
    </form>
</div>

<!-- Сетка книг -->
<div class="books-grid">
    <?php if (empty($books)): ?>
        <div class="empty-state">
            <p>📚 Книги не найдены</p>
        </div>
    <?php else: ?>
        <?php foreach ($books as $book): ?>
            <button type="button" class="book-card" onclick="openBookModal(<?= (int) $book['id'] ?>)" aria-haspopup="dialog">
                <div class="book-cover">
                    <?php if ($book['cover_image']): ?>
                        <img src="<?= escape($book['cover_image']) ?>" alt="<?= escape($book['title']) ?>">
                    <?php else: ?>
                        <div class="book-cover-placeholder">
                            <?= getBookIcon($book['genre']) ?>
                        </div>
                    <?php endif; ?>
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
    <?php endif; ?>
</div>

<!-- Модальное окно: Информация о книге -->
<div id="bookModal" class="modal" role="dialog" aria-modal="true" aria-label="Информация о книге">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal('bookModal')" aria-label="Закрыть окно">&times;</button>
        <div id="bookModalContent">
            <!-- Заполняется через JavaScript -->
        </div>
    </div>
</div>

<!-- Модальное окно: Добавление книги (только для админа) -->
<?php if (isAdmin()): ?>
<div id="addBookModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="addBookModalTitle">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal('addBookModal')" aria-label="Закрыть окно">&times;</button>
        <h2 id="addBookModalTitle">➕ Добавить новую книгу</h2>
        <form id="addBookForm" method="POST" action="api/add-book.php" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
            <div class="form-group">
                <label for="title">Название книги *</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="author">Автор *</label>
                <input type="text" id="author" name="author" required>
            </div>

            <div class="form-group">
                <label for="genre">Жанр *</label>
                <input type="text" id="genre" name="genre" required>
            </div>

            <div class="form-group">
                <label for="isbn">ISBN *</label>
                <input type="text" id="isbn" name="isbn" required pattern="[0-9\-]+">
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addBookModal')">
                    Отмена
                </button>
                <button type="submit" class="btn btn-primary">
                    ➕ Добавить книгу
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="js/books.js?v=20260719"></script>

<?php include 'includes/footer.php'; ?>
