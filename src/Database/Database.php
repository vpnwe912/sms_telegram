<?php
namespace Svobo\SmsConsiderPpUa\Database;

class Database {
    private $connection;

    public function __construct($config) {
        $this->connection = new \mysqli(
            $config['host'],
            $config['user'],
            $config['password'],
            $config['dbname']
        );

        if ($this->connection->connect_error) {
            die('Ошибка подключения (' . $this->connection->connect_errno . ') '
                . $this->connection->connect_error);
        }

        // Устанавливаем кодировку
        $this->connection->set_charset($config['charset']);
    }

    public function getConnection() {
        return $this->connection;
    }

    // Метод для выполнения подготовленных запросов
    public function prepareAndExecute($query, $types = null, $params = []) {
        $stmt = $this->connection->prepare($query);
        if ($stmt === false) {
            throw new \Exception('Ошибка подготовки запроса: ' . $this->connection->error);
        }

        if ($types && $params) {
            // Связываем параметры
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new \Exception('Ошибка выполнения запроса: ' . $stmt->error);
        }

        return $stmt;
    }
}
?>
