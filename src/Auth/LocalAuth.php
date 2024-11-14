<?php
namespace Svobo\SmsConsiderPpUa\Auth;

use Svobo\SmsConsiderPpUa\Database\Database;

class LocalAuth implements AuthInterface {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db->getConnection();
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && password_verify($password, $result['password'])) {
            $_SESSION['user'] = $result;
            return true;
        } else {
            echo "Логин или пароль не верен";
            return false;
        }
    }
}
