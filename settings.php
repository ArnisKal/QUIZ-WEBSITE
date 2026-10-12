<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Список тем: 'имя_в_css' => 'Название на кнопке'
$themes = [
    'light'    => 'Light',
    'midnight' => 'Midnight',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Quizzity</title>

    <!-- Ставим сохранённую тему ДО подключения стилей, чтобы не было вспышки -->
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('theme') || 'light';
    </script>

    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="settings_container">
        <div class="settings_main">
            <h1>Settings</h1>

            <button type="button">General</button>
            <button type="button">Quizzes</button>

            <a href="index.php" aria-label="Close settings">
                <img src="images/close.png" alt="Close">
            </a>

            <div>
                <p>Themes</p>
                <?php foreach ($themes as $key => $name): ?>
                    <button type="button" class="theme_btn" data-theme-value="<?= htmlspecialchars($key) ?>">
                        <?= htmlspecialchars($name) ?>
                    </button>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <script>
        document.querySelectorAll('.theme_btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var theme = btn.dataset.themeValue;
                document.documentElement.dataset.theme = theme;
                localStorage.setItem('theme', theme);
            });
        });
    </script>
</body>
</html>