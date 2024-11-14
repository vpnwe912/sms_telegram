<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    // Если нет, перенаправляем на страницу входа
    header('Location: /');
    exit;
}

// Получаем имя пользователя из сессии
$displayName = htmlspecialchars($_SESSION['user']['cn'][0]);

// Подключаем автозагрузку Composer
require_once __DIR__ . '/../vendor/autoload.php';

use Svobo\SmsConsiderPpUa\Database\Database;
use Svobo\SmsConsiderPpUa\Models\Sms;

// Загружаем конфигурацию
$config = require __DIR__ . '/../config/config.php';


?>

<?php include '../templates/header.php'; ?>
    <main class="container my-5">
        <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <h2 class="h5 mb-0">Настройки</h2>
                        </div>
                        <div class="card-body">
                            
                        </div>
                    </div>
                </div>
        </div>

    </main>


<?php include '../templates/footer.php'; ?>