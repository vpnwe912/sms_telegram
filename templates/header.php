<?php
// Определяем базовый путь для ссылок
$basePath = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>SMS Telegram</title>
    <script>
        // Устанавливаем тему до загрузки CSS
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo $basePath; ?>index.php">SMS Telegram</a>
    <!-- Кнопка-тогглер для мобильных устройств -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Переключить навигацию">
      <span class="navbar-toggler-icon"></span>
    </button>
    <!-- Содержимое навигации -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="/dashboard">Главная</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/sms">SMS</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/profile">Профиль</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/settings">Настройки</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/logout">Выйти</a>
          </li>
        <?php endif; ?>
      </ul>
      <!-- Переключатель темы -->
      <div class="d-flex">
        <button id="theme-toggle" class="btn btn-outline-secondary">🌙</button>
      </div>
    </div>
  </div>
</nav>

<main>