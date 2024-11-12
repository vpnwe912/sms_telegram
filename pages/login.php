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
    
</head>
<body class="d-flex flex-column min-vh-100">

<div class="container mt-5">
    <div class="row justify-content-center">
        <!-- Используем классы col-12 и col-md-6 для адаптивной ширины -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Авторизация</h2>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Логин</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Войти</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('username').focus();
</script>
</body>



