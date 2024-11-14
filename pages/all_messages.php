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

// Определите текущий номер пользователя (получатель)
$currentUserNumber = '380630200978'; // Замените на фактический номер пользователя

// Получаем параметры из GET-запроса
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_date = isset($_GET['search_date']) ? trim($_GET['search_date']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'received_at';
$order = isset($_GET['order']) ? strtoupper(trim($_GET['order'])) : 'DESC';

// Получаем текущую страницу из GET-параметра, по умолчанию 1
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max($currentPage, 1); // Минимум 1

// Количество сообщений на странице
$messagesPerPage = 10;

// Вычисляем смещение
$offset = ($currentPage - 1) * $messagesPerPage;

// Получаем общее количество сообщений
$totalMessages = $smsModel->getSmsCount($search, $search_date);

// Вычисляем общее количество страниц
$totalPages = ceil($totalMessages / $messagesPerPage);

// Получаем сообщения для текущей страницы
$smsList = $smsModel->getSmsPaginated($messagesPerPage, $offset, $sort, $order, $search, $search_date);

/**
 * Функция для построения URL с учётом текущих параметров фильтрации и сортировки.
 *
 * @param int $page Номер страницы.
 * @param string $sort Поле для сортировки.
 * @param string $order Порядок сортировки.
 * @param string $search Поисковый текст.
 * @param string $search_date Поисковая дата.
 * @return string Построенный URL.
 */
function buildUrl($page, $sort, $order, $search, $search_date) {
    // Формируем массив параметров
    $params = [];
    if (!empty($search)) {
        $params['search'] = $search;
    }
    if (!empty($search_date)) {
        $params['search_date'] = $search_date;
    }
    if (!empty($sort)) {
        $params['sort'] = $sort;
    }
    if (!empty($order)) {
        $params['order'] = $order;
    }
    if ($page > 1) {
        $params['page'] = $page;
    }

    // Строим строку запроса
    $query = http_build_query($params);

    // Возвращаем относительный URL
    return 'all_messages' . (!empty($query) ? '?' . $query : '');
}
?>
<?php include '../templates/header.php'; ?>

<main class="container my-5">
    

    <!-- Раздел для отображения всех сообщений -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h2 class="h5 mb-0">Все SMS-сообщения</h2>
                </div>
                <div class="card-body">
                    <!-- Форма поиска и фильтрации -->
                    <form method="GET" action="all_messages" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Поиск по отправителю, получателю или тексту..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="search_date" class="form-control" placeholder="Поиск по дате..." value="<?php echo htmlspecialchars($search_date); ?>">
                        </div>
                    
                       
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-200">Применить</button>
                        </div>
                    </form>
                    <form method="GET" action="all_messages"  id="filterForm" class="row g-3 mb-4">
                        <div class="col-md-2">
                            <select name="sort" class="form-select">
                                <option value="sender" <?php if ($sort === 'sender') echo 'selected'; ?>>Отправитель</option>
                                <option value="received_at" <?php if ($sort === 'received_at') echo 'selected'; ?>>Дата и время</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="order" class="form-select">
                                <option value="ASC" <?php if ($order === 'ASC') echo 'selected'; ?>>По возрастанию</option>
                                <option value="DESC" <?php if ($order === 'DESC') echo 'selected'; ?>>По убыванию</option>
                            </select>
                        </div>
                        <!-- <div class="col-md-1">
                            <button type="submit" class="btn btn-primary">Применить</button>
                        </div> -->
                    </form>

                    <!-- Таблица сообщений -->
                    <?php if (!empty($smsList)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>№</th>
                                        <th>Отправитель</th>
                                        <th>Получатель</th>
                                        <th>Дата и время</th>
                                        <th>Сообщение</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($smsList as $index => $sms): ?>
                                        <?php
                                            // Определяем, отправлено ли сообщение текущим пользователем
                                            $isSent = ($sms['sender'] === $currentUserNumber);
                                            $rowClass = $isSent ? 'table-success' : 'table-secondary';
                                        ?>
                                        <tr class="<?php echo $rowClass; ?>">
                                            <td><?php echo ($offset + $index + 1); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($isSent ? 'Вы' : 'Отправитель: ' . $sms['sender']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($sms['recipient']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($sms['received_at']); ?>
                                            </td>
                                            <td>
                                                <?php echo nl2br(htmlspecialchars($sms['text'])); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Нет SMS-сообщений для отображения.</div>
                    <?php endif; ?>

                    <!-- Пагинация -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <!-- Предыдущая страница -->
                                <li class="page-item <?php if ($currentPage <= 1) echo 'disabled'; ?>">
                                    <a class="page-link" href="<?php echo buildUrl($currentPage - 1, $sort, $order, $search, $search_date); ?>" tabindex="-1">Предыдущая</a>
                                </li>

                                <!-- Номера страниц -->
                                <?php
                                // Определим диапазон отображаемых страниц
                                $maxPagesToShow = 5;
                                $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
                                $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);

                                // Корректируем startPage, если endPage достигло лимита
                                if ($endPage - $startPage + 1 < $maxPagesToShow) {
                                    $startPage = max(1, $endPage - $maxPagesToShow + 1);
                                }

                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?php if ($i == $currentPage) echo 'active'; ?>">
                                        <a class="page-link" href="<?php echo buildUrl($i, $sort, $order, $search, $search_date); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Следующая страница -->
                                <li class="page-item <?php if ($currentPage >= $totalPages) echo 'disabled'; ?>">
                                    <a class="page-link" href="<?php echo buildUrl($currentPage + 1, $sort, $order, $search, $search_date); ?>">Следующая</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Конец раздела для отображения всех сообщений -->
    </main>

    <?php include '../templates/footer.php'; ?>
    <script>
        // Убедитесь, что DOM полностью загружен
        document.addEventListener('DOMContentLoaded', function() {
            // Получаем форму по её ID
            const filterForm = document.getElementById('filterForm');
            
            // Получаем все элементы формы, которые должны инициировать отправку при изменении
            const formElements = filterForm.querySelectorAll('select, input');

            // Функция для отправки формы
            const submitForm = () => {
                filterForm.submit();
            };

            // Добавляем обработчик события 'change' к каждому элементу формы
            formElements.forEach(function(element) {
                element.addEventListener('change', submitForm);
            });
        });
        </script>