<?php
if (!defined('PAGE_TITLE')) {
    define('PAGE_TITLE', 'Библиотека');
}

$currentUser = getCurrentUser();
$overdueCount = 0;

if ($currentUser && $currentUser['role'] === ROLE_READER) {
    $overdueCount = getOverdueCount($currentUser['id']);
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= PAGE_TITLE ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><?= SITE_NAME ?></h1>
                    <span class="subtitle"><?= SITE_SUBTITLE ?></span>
                </div>

                <div class="header-actions">
                    <?php if ($overdueCount > 0): ?>
                        <div class="notification-badge">
                            <span class="badge badge-danger">
                                🔴 <?= $overdueCount ?> просрочено
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="user-menu">
                        <div class="user-avatar">
                            <span class="avatar-circle <?= $currentUser['role'] === ROLE_ADMIN ? 'admin' : 'reader' ?>">
                                <?= getInitials($currentUser['full_name']) ?>
                            </span>
                            <span class="user-name"><?= escape($currentUser['full_name']) ?></span>
                        </div>
                        <a href="logout.php" class="btn-logout" title="Выход">
                            🚪 Выход
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                <li class="<?= $currentPage === 'index' ? 'active' : '' ?>">
                    <a href="index.php">
                        📚 Каталог
                    </a>
                </li>
                <li class="<?= $currentPage === 'search' ? 'active' : '' ?>">
                    <a href="search.php">
                        🔍 Поиск
                    </a>
                </li>
                <li class="<?= $currentPage === 'my-books' ? 'active' : '' ?>">
                    <a href="my-books.php">
                        📖 Мои книги
                    </a>
                </li>
                <?php if (isReader()): ?>
                    <li class="<?= $currentPage === 'my-requests' ? 'active' : '' ?>">
                        <a href="my-requests.php">
                            📋 Мои заявки
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                    <li class="<?= $currentPage === 'management' ? 'active' : '' ?>">
                        <a href="management.php">
                            ⚙️ Управление
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <?php
    // Отображение flash сообщений
    $flash = getFlashMessage();
    if ($flash):
    ?>
        <div class="flash-message flash-<?= $flash['type'] ?>">
            <div class="container">
                <?= escape($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">
