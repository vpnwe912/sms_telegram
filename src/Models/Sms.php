<?php
namespace Svobo\SmsConsiderPpUa\Models;

use Svobo\SmsConsiderPpUa\Database\Database;

class Sms {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database;
    }

    /**
     * Получить последние SMS-сообщения.
     *
     * @param int $limit Количество сообщений для получения.
     * @return array Массив SMS-сообщений.
     */
    public function getLatestSms($limit = 20) {
        $query = "SELECT sender, recipient, text, received_at FROM sms_messages ORDER BY received_at DESC LIMIT ?";
        try {
            $stmt = $this->db->prepareAndExecute($query, 'i', [$limit]);
            $result = $stmt->get_result();
            $smsList = [];
            while ($row = $result->fetch_assoc()) {
                $smsList[] = $row;
            }
            $stmt->close();
            return $smsList;
        } catch (\Exception $e) {
            error_log("Ошибка при получении SMS: " . $e->getMessage());
            return [];
        }
    }
}
?>
