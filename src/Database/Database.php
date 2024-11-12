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
    }

    public function getConnection() {
        return $this->connection;
    }
}
