<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
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

                <div class="set_row">
                    <span class="set_label" id="modeLabel">Mode</span>
                    <div class="set_control">
                        <div class="set_options" role="radiogroup" aria-labelledby="modeLabel">
                            <label class="set_option">
                                <input type="radio" name="mode" value="light" checked>
                                <span>Light</span>
                            </label>
                            <label class="set_option">
                                <input type="radio" name="mode" value="midnight">
                                <span>Midnight</span>
                            </label>
                        </div>
                        <small class="set_note">More modes are coming soon.</small>
                    </div>
                </div>

                <div class="set_row">
                    <label class="set_label" for="language">Language</label>
                    <div class="set_control">
                        <select id="language" class="set_select">
                            <option>English</option>
                            <option>Latvian</option>
                            <option>Рussian</option>
                            <option>Spanish</option>
                            <option>Italiano</option>
                            <option>Ukranian</option>                                
                            <option>Polski</option>
                            <option>Deutsch</option>
                        </select>
                    </div>
                </div>

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
                tabs.forEach(function (t) { t.classList.remove('active'); });
                tab.classList.add('active');

                panels.forEach(function (p) {
                    p.hidden = (p.id !== 'tab-' + tab.dataset.tab);
                });
            });
        });
        var music    = document.getElementById('music');
        var musicOut = document.getElementById('musicOut');

        function updateSlider() {
            musicOut.textContent = music.value;
            music.style.setProperty('--val', music.value + '%');
        }

        music.addEventListener('input', updateSlider);
        updateSlider();
    </script>
</body>
</html>