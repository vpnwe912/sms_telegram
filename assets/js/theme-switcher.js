document.addEventListener('DOMContentLoaded', function () {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;

    // Функция для установки темы
    function setTheme(theme) {
        htmlElement.setAttribute('data-bs-theme', theme);
        themeToggleBtn.textContent = theme === 'light' ? '🌙' : '☀️';
        localStorage.setItem('theme', theme);
    }

    // Получаем сохранённую тему или устанавливаем по умолчанию
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);

    // Обработчик события для переключения темы
    themeToggleBtn.addEventListener('click', function () {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
    });
});
