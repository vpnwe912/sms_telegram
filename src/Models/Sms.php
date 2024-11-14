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

    /**
     * Получить SMS-сообщения с учётом пагинации, сортировки и поиска.
     *
     * @param int $limit Количество сообщений для получения.
     * @param int $offset Смещение для выборки сообщений.
     * @param string $sort Поле для сортировки (sender, received_at).
     * @param string $order Порядок сортировки (ASC, DESC).
     * @param string $search Поисковый текст.
     * @param string $search_date Поисковая дата.
     * @return array Массив SMS-сообщений.
     */
    public function getSmsPaginated($limit = 10, $offset = 0, $sort = 'received_at', $order = 'DESC', $search = '', $search_date = '') {
        // Допустимые поля для сортировки
        $allowedSort = ['sender', 'received_at'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'received_at';
        }

        // Допустимые порядки сортировки
        $allowedOrder = ['ASC', 'DESC'];
        if (!in_array(strtoupper($order), $allowedOrder)) {
            $order = 'DESC';
        }

        // Базовый SQL-запрос
        $query = "SELECT sender, recipient, text, received_at FROM sms_messages";

        // Формируем условия поиска
        $conditions = [];
        $params = [];
        $types = '';

        if (!empty($search)) {
            $conditions[] = "(sender LIKE ? OR recipient LIKE ? OR text LIKE ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'sss';
        }

        if (!empty($search_date)) {
            $conditions[] = "DATE(received_at) = ?";
            $params[] = $search_date;
            $types .= 's';
        }

        // Добавляем условия в запрос, если они есть
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        // Добавление сортировки
        $query .= " ORDER BY $sort $order";

        // Добавление лимита и смещения
        $query .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        $types .= 'ii';

        try {
            $stmt = $this->db->prepareAndExecute($query, $types, $params);
            $result = $stmt->get_result();
            $smsList = [];
            while ($row = $result->fetch_assoc()) {
                $smsList[] = $row;
            }
            $stmt->close();
            return $smsList;
        } catch (\Exception $e) {
            error_log("Ошибка при получении SMS с пагинацией: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получить общее количество SMS-сообщений с учётом поиска.
     *
     * @param string $search Поисковый текст.
     * @param string $search_date Поисковая дата.
     * @return int Общее количество сообщений.
     */
    public function getSmsCount($search = '', $search_date = '') {
        // Базовый SQL-запрос
        $query = "SELECT COUNT(*) as count FROM sms_messages";

        // Формируем условия поиска
        $conditions = [];
        $params = [];
        $types = '';

        if (!empty($search)) {
            $conditions[] = "(sender LIKE ? OR recipient LIKE ? OR text LIKE ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'sss';
        }

        if (!empty($search_date)) {
            $conditions[] = "DATE(received_at) = ?";
            $params[] = $search_date;
            $types .= 's';
        }

        // Добавляем условия в запрос, если они есть
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        try {
            $stmt = $this->db->prepareAndExecute($query, $types, $params);
            $result = $stmt->get_result();
            $count = 0;
            if ($row = $result->fetch_assoc()) {
                $count = isset($row['count']) ? (int)$row['count'] : 0;
            }
            $stmt->close();
            return $count;
        } catch (\Exception $e) {
            error_log("Ошибка при подсчёте SMS: " . $e->getMessage());
            return 0;
        }
    }
}
?>
