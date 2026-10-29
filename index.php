<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/database.php';
require_once 'lang.php';
require_once 'auth.php';
require_once 'site_check.php';
require_once 'theme.php';

$texts = [];
foreach ($pdo->query('SELECT * FROM site_texts') as $row) {
    $texts[$row['text_key']] = tr($row, 'text_value');
}
$blocks = $pdo->query('SELECT * FROM info_blocks ORDER BY sort_order, id')->fetchAll();
$faq    = $pdo->query('SELECT * FROM faq_items ORDER BY sort_order, id')->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzity</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

<header class="header">

   <div class="header_left">
        <div class="menu">
            <button type="button" class="menu_btn" id="menuBtn" aria-label="<?= t('menu') ?>">
                <img src="images/threeBar.png" alt="" width="30">
            </button>

           <div class="menu_list" id="menuList">
                <a href="settings.php"><?= t('settings') ?></a>
                <a href="account.php"><?= t('account') ?></a>
                <a href="liked.php"><?= t('liked') ?></a>
                <a href="#"><?= t('leaderboard') ?></a>
            </div>
        </div>

        <script>
            var menuBtn = document.getElementById('menuBtn')
            var menuList = document.getElementById('menuList')

            menuBtn.addEventListener('click', function(){
               menuList.classList.toggle('open');
            });
        </script>

        <h1>QUIZZITY</h1>
    </div>

    <form action="quizes.php" method="get" class="search_form" role="search">
        <input
            type="search"
            name="q"
            class="search"
            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
            placeholder="<?= t('search') ?>"
            aria-label="<?= t('search') ?>"
        >
    </form>

    <nav class="nav">
        <div class="lang_switch" role="group" aria-label="<?= t('language') ?>">
            <?php foreach ($languages as $code => $name): ?>
                <a href="?lang=<?= $code ?>"
                   title="<?= htmlspecialchars($name) ?>"
                   <?= $code === $lang ? 'class="active" aria-current="true"' : '' ?>><?= strtoupper($code) ?></a>
            <?php endforeach; ?>
        </div>

    <button type="button" class="theme_btn" id="themeBtn" aria-label="Toggle theme">
        <img class="icon_sun"  src="images/sun.png"  alt="" width="22" height="22">
        <img class="icon_moon" src="images/moon.png" alt="" width="22" height="22">
    </button>

        <a href="quizes.php"><?= t('quizzes') ?></a>
        <a href="leaderboards.php"><?= t('leaderboards') ?></a>

        <?php if (isset($_SESSION['user_id'])): ?>

           <a href="account.php" class="avatar"
                title="<?= htmlspecialchars($_SESSION['username']) ?>"
                aria-label="<?= t('account') ?>: <?= htmlspecialchars($_SESSION['username']) ?>">
                <img src="images/user.png" alt="" width="24" height="24">
            </a>

            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="admin/admin.php"><?= t('admin') ?></a>
            <?php endif; ?>

            <a href="logout.php"><?= t('logout') ?></a>

        <?php else: ?>

            <a href="login.php"><?= t('login') ?></a>
            <a href="register.php"><?= t('register') ?></a>

        <?php endif; ?>
    </nav>

</header>

    <div class="ad_main">
        <div class="ad">
            <h1><?= htmlspecialchars($texts['hero_title'] ?? '') ?></h1>
            <p><?= htmlspecialchars($texts['hero_text'] ?? '') ?></p>
            <img src="images/my-summer-car.gif" alt="">
        </div>
    </div>

    <section class="info-blocks">
        <?php foreach ($blocks as $b): ?>
            <article class="info-block">
                <?php if ($b['image']): ?>
                    <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars(tr($b, 'title')) ?>">
                <?php endif; ?>
                <h2><?= htmlspecialchars(tr($b, 'title')) ?></h2>
                <p><?= htmlspecialchars(tr($b, 'body')) ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="faq">
        <h2 class="faq_title"><?= t('faq') ?></h2>
        <?php foreach ($faq as $i => $item): ?>
            <details class="faq_item"<?= $i === 0 ? ' open' : '' ?>>
                <summary>
                    <span><?= htmlspecialchars(tr($item, 'question')) ?></span>
                    <span class="faq_line"></span>
                    <span class="faq_arrow"></span>
                </summary>
                <p class="faq_answer"><?= htmlspecialchars(tr($item, 'answer')) ?></p>
            </details>
        <?php endforeach; ?>
    </section>

   <footer class="footer">
    <nav class="footer_links">
        <a href="https://github.com/ArnisKal/QUIZ-WEBSITE" aria-label="Github"><img src="images/github.png" width="40" height="40" alt="Github"></a>
        <a href="#" aria-label="Twitter"><img src="images/twitter.png" width="40" height="40" alt="Twitter"></a>
        <a href="#" aria-label="Youtube"><img src="images/youtube.png" width="40" height="40" alt="Youtube"></a>
        <a href="#" aria-label="Twitch"><img src="images/twitch.png" width="40" height="40" alt="Twitch"></a>
    </nav>
    <p class="footer_copy">© 2026 QUIZZITY. <?= t('rights') ?></p>
</footer>

<script>
    var themeBtn = document.getElementById('themeBtn');

    themeBtn.addEventListener('click', function () {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var theme  = isDark ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', theme);

        fetch('save_theme.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'theme=' + encodeURIComponent(theme),
            keepalive: true
        }).catch(function (e) { console.error(e); });
    });
</script>
</body>
</html>