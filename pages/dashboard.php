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

// Создаем экземпляр класса Database
$database = new Database($config['db']);

// Создаем экземпляр модели Sms
$smsModel = new Sms($database);

// Получаем последние 20 SMS-сообщений
$smsList = $smsModel->getLatestSms(20);

// Определите текущий номер пользователя (получатель)
$currentUserNumber = '380630200978'; // Замените на фактический номер пользователя
?>
<?php include '../templates/header.php'; ?>

<main class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h2 class="card-title">Добро пожаловать, <?php echo $displayName; ?>!</h2>
                    <p class="card-text">Тут отображаются все сообщения привязаны к номеру <?php echo htmlspecialchars($currentUserNumber); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Раздел для отображения чата -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h2 class="h5 mb-0">SMS-сообщения</h2>
                </div>
                <div class="card-body" id="chat-container">
                    <?php if (!empty($smsList)): ?>
                        <?php foreach ($smsList as $sms): ?>
                            <?php
                                // Определяем, отправлено ли сообщение текущим пользователем
                                $isSent = ($sms['sender'] === $currentUserNumber);
                            ?>
                            <div class="message <?php echo $isSent ? 'sent' : 'received'; ?> mb-3">
                                <!-- Отправитель -->
                                <div class="sender-name">
                                    <?php echo htmlspecialchars($isSent ? 'Вы' : 'Отправитель: ' . $sms['sender']); ?>
                                </div>
                                <!-- Облако сообщения -->
                                <div class="message-content">
                                    <!-- Дата и время -->
                                    <div class="timestamp">
                                        <?php echo htmlspecialchars($sms['received_at']); ?>
                                    </div>
                                    <!-- Текст сообщения -->
                                    <div class="text">
                                        <?php echo nl2br(htmlspecialchars($sms['text'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Нет SMS-сообщений для отображения.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Конец раздела для отображения чата -->
</main>

<?php include '../templates/footer.php'; ?>

<!-- Добавляем JavaScript для автообновления чата -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chat-container');

    // Функция для загрузки последних SMS
    function loadLatestSms() {
        fetch('/pages/fetch_sms.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.sms) {
                    // Очистить текущий чат
                    chatContainer.innerHTML = '';

                    // Добавить сообщения
                    data.sms.forEach((sms, index) => {
                        // Определение, отправлено ли сообщение текущим пользователем
                        const isSent = (sms.sender === '<?php echo $currentUserNumber; ?>');

                        // Создание элементов
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message', isSent ? 'sent' : 'received', 'mb-3');

                        // Отправитель
                        const senderNameDiv = document.createElement('div');
                        senderNameDiv.classList.add('sender-name');
                        senderNameDiv.textContent = isSent ? 'Вы' : 'Отправитель: ' + sms.sender;

                        // Облако сообщения
                        const messageContentDiv = document.createElement('div');
                        messageContentDiv.classList.add('message-content');

                        // Дата и время
                        const timestampDiv = document.createElement('div');
                        timestampDiv.classList.add('timestamp');
                        timestampDiv.textContent = sms.received_at;

                        // Текст сообщения
                        const textDiv = document.createElement('div');
                        textDiv.classList.add('text');
                        textDiv.innerHTML = sms.text.replace(/\n/g, '<br>');

                        // Собираем структуру сообщения
                        messageContentDiv.appendChild(timestampDiv);
                        messageContentDiv.appendChild(textDiv);

                        messageDiv.appendChild(senderNameDiv);
                        messageDiv.appendChild(messageContentDiv);

                        chatContainer.appendChild(messageDiv);
                    });

                    // Прокрутить чат вниз
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            })
            .catch(error => {
                console.error('Ошибка при загрузке SMS:', error);
            });
    }

    // Загрузка SMS при загрузке страницы
    loadLatestSms();

    // Автообновление каждые 7 секунд
    setInterval(loadLatestSms, 7000);
});
</script>
