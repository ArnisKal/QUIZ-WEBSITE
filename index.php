<?php session_start();?>
<?php require_once 'auth.php'; ?>
<?php require_once 'site_check.php'; ?>
<?php
if (!isset($pdo)) {
    require_once 'database.php';   
}

$blocks = $pdo->query('SELECT * FROM info_blocks ORDER BY sort_order, id')->fetchAll();
$faqs   = $pdo->query('SELECT * FROM faq_items ORDER BY sort_order, id')->fetchAll();

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzity</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        try {
            if (localStorage.getItem('theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        } catch (e) {}
    </script>
</head>

<body>

<header class="header">

   <div class="header_left">

        <div class="menu">
            <button type="button" class="menu_btn" id="menuBtn" aria-label="Menu">
                <img src="images/threeBar.png" alt="" width="30">
            </button>

           <div class="menu_list" id="menuList">
                <a href="settings.php">Settings</a>
                <a href="account.php">Account</a>
                <a href="liked.php">Liked quizzes</a>
                <a href="leaderboards.php">Leaderboard</a>
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

    <input type="search" class="search" placeholder="Search quizzes...">

    <nav class="nav">
        <a href="quizes.php">Quizzes</a>
        <a href="leaderboards.php">Leaderboards</a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="account.php" class="avatar"
                title="<?= h($_SESSION['username']) ?>"
                aria-label="Account: <?= h($_SESSION['username']) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/>
                    </svg>
            </a>

            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="admin/admin.php">Admin</a>
            <?php endif; ?>

            <a href="logout.php">Logout</a>

        <?php else: ?>

            <a href="login.php">Login</a>
            <a href="register.php">Register</a>

        <?php endif; ?>

    </nav>

</header>

    <div class="ad_main">
        <div class="ad">
            <h1>Welcome to QUIZZITY!</h1>
            <p>The place where you can be yourself!</p>
            <img src="images/my-summer-car.gif">
        </div>
    </div>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <p style="text-align:center; margin:10px;">
            <a href="admin/content.php">&#9998; Edit homepage content</a>
        </p>
    <?php endif; ?>

    <section class="info-blocks">

        <?php foreach ($blocks as $b): ?>
            <article class="info-block">
                <?php if (!empty($b['image'])): ?>
                    <img src="<?= h($b['image']) ?>" alt="<?= h($b['title']) ?>">
                <?php endif; ?>
                <h2><?= h($b['title']) ?></h2>
                <p><?= h($b['description']) ?></p>
            </article>
        <?php endforeach; ?>

    </section>

    <section class="faq">
    <h2 class="faq_title">FAQ</h2>

    <?php foreach ($faqs as $i => $f): ?>
        <details class="faq_item"<?= $i === 0 ? ' open' : '' ?>>
            <summary>
                <span><?= h($f['question']) ?></span>
                <span class="faq_line"></span>
                <span class="faq_arrow"></span>
            </summary>
            <p class="faq_answer"><?= nl2br(h($f['answer'])) ?></p>
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
    <p class="footer_copy">© 2026 QUIZZITY. All rights reserved.</p>
</footer>

</body>
</html>