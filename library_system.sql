-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Июл 15 2026 г., 15:14
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `library_system`
--

-- --------------------------------------------------------

--
-- Структура таблицы `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `genre` varchar(100) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `status` enum('available','issued') NOT NULL DEFAULT 'available',
  `cover_image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `genre`, `isbn`, `status`, `cover_image`, `description`, `created_at`, `updated_at`) VALUES
(1, '1984', 'Джордж Оруэлл', 'Антиутопия', '978-5-17-098825-1', 'available', NULL, 'Антиутопия Оруэлла - пронзительная сатира на тоталитарное государство и предупреждение об опасности тотального контроля.', '2026-02-24 12:20:32', NULL),
(2, 'Война и мир', 'Лев Толстой', 'Классика', '978-5-17-982345-1', 'available', NULL, 'Эпический роман-эпопея о русском обществе в эпоху войн против Наполеона.', '2026-02-24 12:20:32', NULL),
(3, 'Алхимик', 'Пауло Коэльо', 'Философия', '978-5-17-098826-8', 'issued', NULL, 'Философская притча о юноше, который отправился навстречу своей мечте.', '2026-02-24 12:20:32', '2026-02-24 12:41:43'),
(4, 'Убить пересмешника', 'Харпер Ли', 'Классика', '978-5-17-098827-5', 'available', NULL, 'Классический роман о расовой несправедливости в американском Юге 1930-х годов.', '2026-02-24 12:20:32', NULL),
(5, 'Мастер и Маргарита', 'Михаил Булгаков', 'Классика', '978-5-17-098828-2', 'issued', NULL, 'Величайший роман XX века о любви, добре и зле, свободе и творчестве.', '2026-02-24 12:20:32', NULL),
(6, 'Гарри Поттер и философский камень', 'Дж. К. Роулинг', 'Фэнтези', '978-5-17-098829-9', 'available', NULL, 'Первая книга серии о мальчике-волшебнике и его приключениях в школе магии Хогвартс.', '2026-02-24 12:20:32', NULL),
(7, 'Три товарища', 'Эрих Мария Ремарк', 'Проза', '978-5-17-098830-5', 'issued', NULL, 'Роман о дружбе, любви и жизни в послевоенной Германии.', '2026-02-24 12:20:32', NULL),
(8, 'Дюна', 'Фрэнк Герберт', 'Фантастика', '978-5-17-098831-2', 'issued', NULL, 'Эпическая научная фантастика о планете-пустыне и борьбе за власть во вселенной.', '2026-02-24 12:20:32', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `reader_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `return_date` date NOT NULL,
  `actual_return_date` date DEFAULT NULL,
  `extended_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','returned','overdue') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Триггеры `loans`
--
DELIMITER $$
CREATE TRIGGER `after_loan_insert` AFTER INSERT ON `loans` FOR EACH ROW BEGIN
    IF NEW.status = 'active' THEN
        UPDATE books SET status = 'issued' WHERE id = NEW.book_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_loan_update` AFTER UPDATE ON `loans` FOR EACH ROW BEGIN
    IF NEW.status = 'returned' AND OLD.status != 'returned' THEN
        UPDATE books SET status = 'available' WHERE id = NEW.book_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `reader_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_id` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('reader','admin') NOT NULL DEFAULT 'reader',
  `full_name` varchar(255) NOT NULL,
  `ticket_number` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `full_name`, `ticket_number`, `created_at`, `updated_at`) VALUES
(8, 'admin@library.ru', '$2y$10$U5gk/7Aaa3NqOP1feph42.Vq3s8DisG8eTzvyYsaSSj1zcHBfR1TS', 'admin', 'Kurchatov Alexander Romanovich', 'R31335', '2026-07-15 20:10:53', '2026-07-15 20:11:53'),
(9, 'petrov@mail.ru', '$2y$10$JEmYAGjVtqr0Tkr5/9FHGOsNJ0/ij.T/QPn/N7RCWrNObDYrAxA2q', 'reader', 'Петров Алексей', 'R81171', '2026-07-15 20:13:47', NULL);

--
-- Триггеры `users`
--
DELIMITER $$
CREATE TRIGGER `before_user_insert` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'reader' AND NEW.ticket_number IS NULL THEN
        SET NEW.ticket_number = CONCAT('R', LPAD(FLOOR(RAND() * 99999), 5, '0'));
    END IF;
END
$$
DELIMITER ;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_author` (`author`),
  ADD KEY `idx_genre` (`genre`),
  ADD KEY `idx_isbn` (`isbn`),
  ADD KEY `idx_status` (`status`);

--
-- Индексы таблицы `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_reader` (`reader_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_return_date` (`return_date`);

--
-- Индексы таблицы `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_book` (`book_id`),
  ADD KEY `idx_reader` (`reader_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_admin` (`admin_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_ticket` (`ticket_number`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`reader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`reader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
