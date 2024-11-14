<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Подключаем автозагрузку Composer
require_once __DIR__ . '/../vendor/autoload.php';

use Svobo\SmsConsiderPpUa\Database\Database;
use Svobo\SmsConsiderPpUa\Models\Sms;

// Загружаем конфигурацию
$config = require __DIR__ . '/../config/config.php';

// Создаем экземпляр класса Database
$database = new Database($config['db']);

// Создаем экземпляр модели Sms
$smsModel = new Sms($database);

// Получаем последние 20 SMS-сообщений
$smsList = $smsModel->getLatestSms(15);

// Устанавливаем заголовок как JSON
header('Content-Type: application/json');

// Возвращаем данные
echo json_encode(['sms' => array_reverse($smsList)]); // array_reverse для отображения старых сообщений сверху
?>
