<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/theme.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Quizzity</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="set_page">
        <div class="set_card">

            <div class="set_head">
                <h1>Settings</h1>

                <div class="set_tabs">
                    <button type="button" class="set_tab active" data-tab="general">General</button>
                    <button type="button" class="set_tab" data-tab="quizzes">Quizzes</button>
                </div>

                <a href="index.php" class="set_close" aria-label="Close settings">✕</a>
            </div>

            <div class="set_body set_panel" id="tab-general">
                <p class="set_empty">General settings will appear here.</p>
            </div>

            <div class="set_body set_panel" id="tab-quizzes" hidden>
                <p class="set_empty">Quiz settings will appear here.</p>
            </div>

        </div>
    </main>

<script>
    var tabs   = document.querySelectorAll('.set_tab');
    var panels = document.querySelectorAll('.set_panel');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) {
                t.classList.toggle('active', t === tab);
            });
            panels.forEach(function (p) {
                p.hidden = p.id !== 'tab-' + tab.dataset.tab;
            });
        });
    });
</script>
</body>
</html>