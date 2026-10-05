<?php session_start(); ?> <!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quizzes</title>

<link rel="stylesheet" href="styles.css">

</head> <body> <header class="header">
<a href="index.php" class="logo">
    QUIZZITY
</a>

<form action="search.php" method="get" class="search_form">

    <input
        type="search"
        name="q"
        class="search"
        placeholder="Search quizzes..."
        aria-label="Search quizzes"
    >

</form>

<nav class="nav">

    <a href="leaderboards.php">
        Leaderboards
    </a>

    <?php if (isset($_SESSION['user_id'])): ?>

        <span class="welcome">
            Hi, <?= htmlspecialchars($_SESSION['username']) ?>
        </span>

        <a href="logout.php">
            Logout
        </a>

    <?php else: ?>

        <a href="login.php">
            Login
        </a>

        <a href="register.php" class="nav_cta">
            Sign up
        </a>

    <?php endif; ?>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="admin/admin.php">Admin</a>
    <?php endif; ?>

</nav>

</header> <div class="filters" role="group" aria-label="Quiz filters">
<button
    class="filter_btn active"
    data-filter="all"
    aria-pressed="true">
    All
</button>

<button
    class="filter_btn"
    data-filter="official"
    aria-pressed="false">
    Official
</button>

<button
    class="filter_btn"
    data-filter="created"
    aria-pressed="false">
    Created
</button>

</div> <div class="section_container">

<article class="section" data-type="official">

    <div class="section_content">

        <h2>
            Memes
        </h2>

        <p>
            The memes that know everyone!
        </p>

        <div class="section_actions">

            <a
                href="quiz.php?id=1"
                class="section_play_btn">

                QUIZZY!

            </a>

        </div>

    </div>

    <img
        class="section_img"
        src="images/arriba.png"
        alt="Memes">

</article>

<article class="section" data-type="official">

    <div class="section_content">

        <h2>
            Movies
        </h2>

        <p>
            Became the most popular movie star!
        </p>

        <div class="section_actions">

            <a
                href="quiz.php?id=2"
                class="section_play_btn">

                QUIZZY!

            </a>

        </div>

    </div>

</article>



<article class="section" data-type="official">

    <div class="section_content">

        <h2>
            Games
        </h2>

        <p>
            Test your gaming knowledge!
        </p>

        <div class="section_actions">

            <a
                href="quiz.php?id=3"
                class="section_play_btn">

                QUIZZY!

            </a>

        </div>

    </div>

    <img
        class="section_img"
        src="images/rdr.png"
        alt="Games">

</article>


<article class="section" data-type="official">

    <div class="section_content">

        <h2>
            IT
        </h2>

        <p>
            Become the genius in IT technologies!
        </p>

        <div class="section_actions">

            <a
                href="quiz.php?id=4"
                class="section_play_btn">

                QUIZZY!

            </a>

        </div>

    </div>

</article>


<article class="section" data-type="official">

    <div class="section_content">

        <h2>
            Entertainment
        </h2>

        <p>
            Test your entertainment knowledge!
        </p>

        <div class="section_actions">

            <a
                href="quiz.php?id=5"
                class="section_play_btn">

                QUIZZY!

            </a>

        </div>

    </div>

    <img
        class="section_img"
        src="images/earth.jpg"
        alt="Entertainment">

</article>

</div> </body> </html>