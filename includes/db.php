<?php
/**
 * Класс для работы с базой данных
 */

require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            http_response_code(500);
            exit('Сервис временно недоступен. Попробуйте обновить страницу позже.');
        }
    }

    /**
     * Получить экземпляр класса (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Получить соединение с базой данных
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Выполнить SELECT запрос
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Выполнить INSERT, UPDATE, DELETE запрос
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить ID последней вставленной записи
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Получить одну запись
     */
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database fetchOne error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Начать транзакцию
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Подтвердить транзакцию
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Откатить транзакцию
     */
    public function rollback() {
        return $this->connection->rollback();
    }
}

// Получить экземпляр базы данных
function getDB() {
    return Database::getInstance();
}
?>
