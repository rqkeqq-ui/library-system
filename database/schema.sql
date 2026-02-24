-- ================================================
-- БИБЛИОТЕКА - СХЕМА БАЗЫ ДАННЫХ
-- ================================================

-- Удаление таблиц если существуют (для пересоздания)
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS loans;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

-- ================================================
-- ТАБЛИЦА: users (Пользователи)
-- ================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('reader', 'admin') NOT NULL DEFAULT 'reader',
    full_name VARCHAR(255) NOT NULL,
    ticket_number VARCHAR(20) UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_ticket (ticket_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- ТАБЛИЦА: books (Книги)
-- ================================================
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('available', 'issued') NOT NULL DEFAULT 'available',
    cover_image VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_author (author),
    INDEX idx_genre (genre),
    INDEX idx_isbn (isbn),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- ТАБЛИЦА: loans (Выдачи книг)
-- ================================================
CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    reader_id INT NOT NULL,
    issue_date DATE NOT NULL,
    return_date DATE NOT NULL,
    actual_return_date DATE DEFAULT NULL,
    extended_count INT NOT NULL DEFAULT 0,
    status ENUM('active', 'returned', 'overdue') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (reader_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_book (book_id),
    INDEX idx_reader (reader_id),
    INDEX idx_status (status),
    INDEX idx_return_date (return_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- ТАБЛИЦА: requests (Заявки на книги)
-- ================================================
CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    reader_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    admin_id INT DEFAULT NULL,
    processed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (reader_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_book (book_id),
    INDEX idx_reader (reader_id),
    INDEX idx_status (status),
    INDEX idx_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- ТРИГГЕР: Автоматическое создание номера билета для читателей
-- ================================================
DELIMITER $$

CREATE TRIGGER before_user_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.role = 'reader' AND NEW.ticket_number IS NULL THEN
        SET NEW.ticket_number = CONCAT('R', LPAD(FLOOR(RAND() * 99999), 5, '0'));
    END IF;
END$$

DELIMITER ;

-- ================================================
-- ТРИГГЕР: Автоматическое обновление статуса книги при выдаче
-- ================================================
DELIMITER $$

CREATE TRIGGER after_loan_insert
AFTER INSERT ON loans
FOR EACH ROW
BEGIN
    IF NEW.status = 'active' THEN
        UPDATE books SET status = 'issued' WHERE id = NEW.book_id;
    END IF;
END$$

DELIMITER ;

-- ================================================
-- ТРИГГЕР: Автоматическое обновление статуса книги при возврате
-- ================================================
DELIMITER $$

CREATE TRIGGER after_loan_update
AFTER UPDATE ON loans
FOR EACH ROW
BEGIN
    IF NEW.status = 'returned' AND OLD.status != 'returned' THEN
        UPDATE books SET status = 'available' WHERE id = NEW.book_id;
    END IF;
END$$

DELIMITER ;
