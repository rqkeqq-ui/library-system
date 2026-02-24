-- ================================================
-- БИБЛИОТЕКА - ТЕСТОВЫЕ ДАННЫЕ
-- ================================================

-- ================================================
-- ПОЛЬЗОВАТЕЛИ
-- ================================================

-- Администраторы (пароль: admin123)
INSERT INTO users (email, password_hash, role, full_name, ticket_number) VALUES
('admin@library.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Администратор Системы', NULL),
('admin2@library.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Второй Администратор', NULL);

-- Читатели (пароль: reader123)
INSERT INTO users (email, password_hash, role, full_name) VALUES
('petrov@mail.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reader', 'Петров Иван Петрович'),
('sidorova@mail.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reader', 'Сидорова Мария Александровна'),
('kozlov@mail.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reader', 'Козлов Алексей Николаевич');

-- Обновление номеров билетов для читателей (если триггер не сработал)
UPDATE users SET ticket_number = '12345' WHERE email = 'petrov@mail.ru';
UPDATE users SET ticket_number = '12346' WHERE email = 'sidorova@mail.ru';
UPDATE users SET ticket_number = '12347' WHERE email = 'kozlov@mail.ru';

-- ================================================
-- КНИГИ
-- ================================================

INSERT INTO books (title, author, genre, isbn, status, description) VALUES
-- Доступные книги
('1984', 'Джордж Оруэлл', 'Антиутопия', '978-5-17-098825-1', 'available',
 'Антиутопия Оруэлла - пронзительная сатира на тоталитарное государство и предупреждение об опасности тотального контроля.'),

('Война и мир', 'Лев Толстой', 'Классика', '978-5-17-982345-1', 'available',
 'Эпический роман-эпопея о русском обществе в эпоху войн против Наполеона.'),

('Алхимик', 'Пауло Коэльо', 'Философия', '978-5-17-098826-8', 'available',
 'Философская притча о юноше, который отправился навстречу своей мечте.'),

('Убить пересмешника', 'Харпер Ли', 'Классика', '978-5-17-098827-5', 'available',
 'Классический роман о расовой несправедливости в американском Юге 1930-х годов.'),

-- Выданные книги
('Мастер и Маргарита', 'Михаил Булгаков', 'Классика', '978-5-17-098828-2', 'issued',
 'Величайший роман XX века о любви, добре и зле, свободе и творчестве.'),

('Гарри Поттер и философский камень', 'Дж. К. Роулинг', 'Фэнтези', '978-5-17-098829-9', 'available',
 'Первая книга серии о мальчике-волшебнике и его приключениях в школе магии Хогвартс.'),

('Три товарища', 'Эрих Мария Ремарк', 'Проза', '978-5-17-098830-5', 'issued',
 'Роман о дружбе, любви и жизни в послевоенной Германии.'),

('Дюна', 'Фрэнк Герберт', 'Фантастика', '978-5-17-098831-2', 'issued',
 'Эпическая научная фантастика о планете-пустыне и борьбе за власть во вселенной.');

-- ================================================
-- ВЫДАЧИ КНИГ
-- ================================================

-- Получаем ID пользователей и книг
SET @petrov_id = (SELECT id FROM users WHERE email = 'petrov@mail.ru');
SET @sidorova_id = (SELECT id FROM users WHERE email = 'sidorova@mail.ru');

SET @master_book_id = (SELECT id FROM books WHERE title = 'Мастер и Маргарита');
SET @tovarisch_book_id = (SELECT id FROM books WHERE title = 'Три товарища');
SET @dune_book_id = (SELECT id FROM books WHERE title = 'Дюна');

-- Просроченная выдача (Петров - Мастер и Маргарита)
INSERT INTO loans (book_id, reader_id, issue_date, return_date, status) VALUES
(@master_book_id, @petrov_id, DATE_SUB(CURDATE(), INTERVAL 19 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'overdue');

-- Активная выдача в срок (Сидорова - Три товарища)
INSERT INTO loans (book_id, reader_id, issue_date, return_date, status) VALUES
(@tovarisch_book_id, @sidorova_id, DATE_SUB(CURDATE(), INTERVAL 9 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'active');

-- Еще одна выдача (для примера истории)
INSERT INTO loans (book_id, reader_id, issue_date, return_date, actual_return_date, status) VALUES
(@dune_book_id, @petrov_id, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 26 DAY), DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'returned');

-- ================================================
-- ЗАЯВКИ НА КНИГИ
-- ================================================

-- Несколько тестовых заявок
INSERT INTO requests (book_id, reader_id, status) VALUES
((SELECT id FROM books WHERE title = '1984'), @petrov_id, 'pending'),
((SELECT id FROM books WHERE title = 'Война и мир'), @sidorova_id, 'pending');

-- ================================================
-- ИТОГОВАЯ ИНФОРМАЦИЯ
-- ================================================

SELECT '=== ПОЛЬЗОВАТЕЛИ ===' AS '';
SELECT id, email, role, full_name, ticket_number FROM users;

SELECT '' AS '';
SELECT '=== КНИГИ ===' AS '';
SELECT id, title, author, genre, status FROM books;

SELECT '' AS '';
SELECT '=== АКТИВНЫЕ ВЫДАЧИ ===' AS '';
SELECT
    l.id,
    b.title AS book,
    u.full_name AS reader,
    l.issue_date,
    l.return_date,
    DATEDIFF(CURDATE(), l.return_date) AS days_overdue,
    l.status
FROM loans l
JOIN books b ON l.book_id = b.id
JOIN users u ON l.reader_id = u.id
WHERE l.status IN ('active', 'overdue');

SELECT '' AS '';
SELECT '=== ЗАЯВКИ ===' AS '';
SELECT
    r.id,
    b.title AS book,
    u.full_name AS reader,
    r.status,
    r.created_at
FROM requests r
JOIN books b ON r.book_id = b.id
JOIN users u ON r.reader_id = u.id
WHERE r.status = 'pending';

-- ================================================
-- УЧЕТНЫЕ ДАННЫЕ ДЛЯ ВХОДА
-- ================================================

SELECT '' AS '';
SELECT '=== УЧЕТНЫЕ ДАННЫЕ ДЛЯ ВХОДА ===' AS '';
SELECT '---' AS '';
SELECT 'АДМИНИСТРАТОР:' AS '';
SELECT 'Email: admin@library.ru' AS '';
SELECT 'Пароль: admin123' AS '';
SELECT '---' AS '';
SELECT 'ЧИТАТЕЛЬ (Петров):' AS '';
SELECT 'Email: petrov@mail.ru' AS '';
SELECT 'Пароль: reader123' AS '';
SELECT '---' AS '';
SELECT 'ЧИТАТЕЛЬ (Сидорова):' AS '';
SELECT 'Email: sidorova@mail.ru' AS '';
SELECT 'Пароль: reader123' AS '';
