<?php session_start();?>
<?php require_once 'auth.php'; ?>
<?php require_once 'site_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
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
            <button type="button" class="menu_btn" id="menuBtn" aria-label="Menu">
                <img src="images/threeBar.png" alt="" width="30">
            </button>

           <div class="menu_list" id="menuList">
                <a href="settings.php">Settings</a>
                <a href="language.php"></a>
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
                title="<?= htmlspecialchars($_SESSION['username']) ?>"
                aria-label="Account: <?= htmlspecialchars($_SESSION['username']) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/>
                    </svg>
            </a>

            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="admin/admin.php">Admin</a>
            <?php endif; ?>

            <a href="logout.php">Logout
                <img src="images/logout.png">
            </a>

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

    <section class="info-blocks">

        <article class="info-block">
            <img src="images/craft.jpg" alt="Create Quizzes">
            <h2>Create Quizzes</h2>
            <p>Build your own quizzes in minutes and share them with the world.</p>
        </article>

        <article class="info-block">
            <img src="images/" alt="Play &amp; Learn">
            <h2>Play &amp; Learn</h2>
            <p>Try quizzes made by the community on any topic.</p>
        </article>

        <article class="info-block">
            <img src="images/leader.jpeg" alt="Climb the Leaderboard">
            <h2>Climb the Leaderboard</h2>
            <p>Score points, beat other players and become number one.</p>
        </article>

        <article class="info-block">
            <img src="images/" alt="Join the Community">
            <h2>Join the QUIZZITY</h2>
            <p>Join our team, to be yourself!</p>
        </article>

    </section>

    <section class="faq">
    <h2 class="faq_title">FAQ</h2>

    <details class="faq_item" open>
        <summary>
            <span>What is Quizzity?</span>
            <span class="faq_line"></span>
            <span class="faq_arrow"></span>
        </summary>
        <p class="faq_answer">
            Quizzity is an online quiz platform where you can play quizzes
            made by us and compete on the leaderboards.
        </p>
    </details>

    <details class="faq_item">
        <summary>
            <span>How I can create my own quizz?</span>
            <span class="faq_line"></span>
            <span class="faq_arrow"></span>
        </summary>
        <p class="faq_answer">
            For now you can't, only admin can create our quizzes!
        </p>
    </details>

     <details class="faq_item">
        <summary>
            <span>Do I need to register my account to play Quizzity?</span>
            <span class="faq_line"></span>
            <span class="faq_arrow"></span>
        </summary>
        <p class="faq_answer">
            Yes, you do. You don't have necessarily have to register using Google.
        </p>
    </details>       
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
