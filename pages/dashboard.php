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
?>

<?php include '../templates/header.php'; ?>




<main class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h1 class="card-title">Добро пожаловать, <?php echo $displayName; ?>!</h1>
                    <p class="card-text">Это ваша панель управления. Здесь вы можете управлять своими настройками, просматривать SMS и многое другое.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../templates/footer.php'; ?>


