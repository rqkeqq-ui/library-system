-- ================================================
-- БИБЛИОТЕКА - ТЕСТОВЫЕ ДАННЫЕ
-- ================================================

-- ================================================
-- ПОЛЬЗОВАТЕЛИ
-- ================================================

-- Демонстрационный администратор (пароль: admin123)
INSERT INTO users (email, password_hash, role, full_name, ticket_number) VALUES
('admin@library.test', '$2y$10$713c4odd13kXeLUy2dIk7eK0pZjGWSMziXeMKXjOEM5.TUe5Rpehm', 'admin', 'Демо Администратор', NULL);

-- Демонстрационные читатели (пароль: reader123)
INSERT INTO users (email, password_hash, role, full_name) VALUES
('reader@library.test', '$2y$10$xmDqzwRu5mP4cu0SCThhK.5Pi/SBZzsRC5FriU03ybyYXYTd/IbIq', 'reader', 'Тестовый Читатель'),
('reader2@library.test', '$2y$10$xmDqzwRu5mP4cu0SCThhK.5Pi/SBZzsRC5FriU03ybyYXYTd/IbIq', 'reader', 'Демо Пользователь');

-- Обновление номеров билетов для читателей (если триггер не сработал)
UPDATE users SET ticket_number = 'R10001' WHERE email = 'reader@library.test';
UPDATE users SET ticket_number = 'R10002' WHERE email = 'reader2@library.test';

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
SET @reader_id = (SELECT id FROM users WHERE email = 'reader@library.test');
SET @reader2_id = (SELECT id FROM users WHERE email = 'reader2@library.test');

SET @master_book_id = (SELECT id FROM books WHERE title = 'Мастер и Маргарита');
SET @tovarisch_book_id = (SELECT id FROM books WHERE title = 'Три товарища');
SET @dune_book_id = (SELECT id FROM books WHERE title = 'Дюна');

-- Просроченная выдача для демонстрации
INSERT INTO loans (book_id, reader_id, issue_date, return_date, status) VALUES
(@master_book_id, @reader_id, DATE_SUB(CURDATE(), INTERVAL 19 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'overdue');

-- Активная выдача в срок для демонстрации
INSERT INTO loans (book_id, reader_id, issue_date, return_date, status) VALUES
(@tovarisch_book_id, @reader2_id, DATE_SUB(CURDATE(), INTERVAL 9 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'active');

-- Еще одна выдача (для примера истории)
INSERT INTO loans (book_id, reader_id, issue_date, return_date, actual_return_date, status) VALUES
(@dune_book_id, @reader_id, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 26 DAY), DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'returned');

-- ================================================
-- ЗАЯВКИ НА КНИГИ
-- ================================================

-- Несколько тестовых заявок
INSERT INTO requests (book_id, reader_id, status) VALUES
((SELECT id FROM books WHERE title = '1984'), @reader_id, 'pending'),
((SELECT id FROM books WHERE title = 'Война и мир'), @reader2_id, 'pending');
