<?php if (isset($_GET['zd_echo'])) exit($_GET['zd_echo']); ?>
<?php
// Включаем отображение ошибок (только для разработки)
// В продакшен-среде рекомендуется отключить отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Загружаем конфигурацию
$config_path = __DIR__ . '/../config/config.php';
if (!file_exists($config_path)) {
    error_log("Configuration file not found at: $config_path");
    exit('Configuration file not found.');
}
$config = require $config_path;

// Подключение к базе данных с использованием PDO
try {
    $db = $config['db'];
    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['password']);
    // Устанавливаем режим ошибок PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Логирование ошибки подключения
    error_log("Database connection failed: " . $e->getMessage());
    exit('Ошибка подключения к базе данных.');
}

// Функция для записи логов (опционально)
/* function logMessage($filename, $message) {
    $date = date('Y-m-d H:i:s');
    file_put_contents(__DIR__ . '/logs/' . $filename, "[$date] $message\n", FILE_APPEND);
} */
function logMessage($filename, $message) {
    $date = date('Y-m-d');
    $logDir = __DIR__ . '/../logs/';
    
    // Убедитесь, что директория логов существует
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $fullPath = $logDir . $filename . '_' . $date . '.txt';
    $formattedMessage = "[$date " . date('H:i:s') . "] $message\n";
    file_put_contents($fullPath, $formattedMessage, FILE_APPEND);
}

//Удаляет лог-файлы старше указанного количества дней.
function cleanupOldLogs($logDir, $days = 1) {
    if (!is_dir($logDir)) {
        return;
    }
    
    $files = glob($logDir . '*.txt');
    $now = time();
    $expiry = $now - ($days * 86400); // 86400 секунд в дне
    
    foreach ($files as $file) {
        if (is_file($file)) {
            if (filemtime($file) < $expiry) {
                unlink($file);
                // Опционально: логируем удаление файла
                // logMessage('cleanup_log', "Удалён старый лог-файл: " . basename($file));
            }
        }
    }
}

// Логируем заголовки запроса (опционально)
$headers = getallheaders();
logMessage('headers_log.txt', print_r($headers, true));

// Получаем сырые данные из входящего запроса
$rawPostData = file_get_contents('php://input');

// Логируем сырые данные (опционально)
logMessage('raw_post_log.txt', $rawPostData);

// Определяем тип содержимого
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

// Инициализируем массив для данных
$data = [];

// Разбираем данные в зависимости от типа содержимого
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode($rawPostData, true);
    logMessage('parsed_data_log.txt', 'JSON Decoded: ' . print_r($data, true));
} elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
    parse_str($rawPostData, $data);
    logMessage('parsed_data_log.txt', 'URL-Encoded Parsed: ' . print_r($data, true));
} else {
    // Попытка использовать $_POST
    $data = $_POST;
    logMessage('parsed_data_log.txt', '$_POST Data: ' . print_r($data, true));
}

// Для отладки: Выводим полученные данные (опционально)
// В продакшен-среде рекомендуется удалить или закомментировать
header('Content-Type: text/plain');
echo "Parsed Data:\n";
print_r($data);

// Проверяем наличие параметра 'event'
if (isset($data['event']) && $data['event'] === 'SMS') {
    // Извлекаем остальные параметры
    $event = $data['event'];
    $result = isset($data['result']) ? $data['result'] : '';

    // Распарсим JSON из 'result'
    $resultData = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage('parsed_data_log.txt', 'JSON Decoding Error: ' . json_last_error_msg());
        echo "JSON Decoding Error\n";
        exit('Invalid JSON in result parameter');
    }

    // Извлекаем поля из результата
    $sender = isset($resultData['caller_id']) ? $resultData['caller_id'] : '';
    $recipient = isset($resultData['caller_did']) ? $resultData['caller_did'] : '';
    $text = isset($resultData['text']) ? $resultData['text'] : '';
    // В данном случае 'signature' отсутствует в данных

    // Если у вас есть способ получать подпись, добавьте ее сюда
    $signature = isset($data['signature']) ? $data['signature'] : '';

    // Логируем извлеченные данные
    logMessage('parsed_data_log.txt', 'Extracted Data: ' . print_r([
        'sender' => $sender,
        'recipient' => $recipient,
        'text' => $text,
        'signature' => $signature
    ], true));

    // Проверка подписи (если она существует)
    if (!empty($signature)) {
        // Обрабатываем параметр 'result' для подписи
        $resultString = json_encode($resultData);

        // Получаем секретный ключ из конфигурации
        $api_secret = $config['zadarma']['api_secret'];

        // Вычисляем подпись
        $signatureTest = base64_encode(hash_hmac('sha1', $resultString, $api_secret, true));

        // Логируем подписи (опционально)
        logMessage('signature_log.txt', "Computed: $signatureTest | Received: $signature");

        // Проверяем подпись
        if ($signature !== $signatureTest) {
            // Неверная подпись
            logMessage('signature_log.txt', "Invalid signature. SMS не обработано.");
            echo "Invalid signature\n";
            exit;
        }
    } else {
        // Если подпись отсутствует, можно либо выйти с ошибкой, либо продолжить
        // Для демонстрации продолжим без проверки подписи
        logMessage('signature_log.txt', "No signature provided. Skipping signature verification.");
    }

    // Проверяем, что необходимые поля присутствуют
    if (empty($sender) || empty($recipient) || empty($text)) {
        logMessage('sms_log.txt', "Missing required fields: sender, recipient, or text.");
        echo "Missing required fields\n";
        exit('Missing required fields');
    }

    // Подготовка данных для вставки в базу данных
    $insertData = [
        ':sender'      => $sender,
        ':recipient'   => $recipient,
        ':text'        => $text,
        ':received_at' => date('Y-m-d H:i:s'), // Используем текущее время
    ];

    // Вставляем данные в таблицу sms_messages
    try {
        $stmt = $pdo->prepare("INSERT INTO sms_messages (sender, recipient, text, received_at) VALUES (:sender, :recipient, :text, :received_at)");
        $stmt->execute($insertData);
        logMessage('sms_log.txt', "SMS от $sender успешно сохранено.");
        echo "SMS обработано и сохранено в базе данных.\n";
    } catch (PDOException $e) {
        // Логирование ошибки вставки
        logMessage('sms_log.txt', "Ошибка при сохранении SMS: " . $e->getMessage());
        echo "Ошибка при сохранении SMS.\n";
    }
    cleanupOldLogs(__DIR__ . '/logs/', 1); // Удалить файлы старше 7 дней
} else {
    // Неверное событие или отсутствуют данные
    $receivedEvent = isset($data['event']) ? $data['event'] : 'No event parameter';
    logMessage('event_log.txt', "Event received: $receivedEvent");
    echo "Event received: $receivedEvent\n";
    exit('Invalid event or missing data');
}
?>
