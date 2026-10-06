<?php
session_start();
require_once 'database.php';

// Only quizzes that already have at least one question are shown to players
$quizzes = $pdo->query("
    SELECT q.*
    FROM quizzes q
    WHERE EXISTS (SELECT 1 FROM questions qs WHERE qs.quiz_id = q.id)
    ORDER BY q.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes</title>

    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">

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

</header>

<div class="filters" role="group" aria-label="Quiz filters">
    <button class="filter_btn active" data-filter="all" aria-pressed="true">
        All
    </button>

    <button class="filter_btn" data-filter="official" aria-pressed="false">
        Official
    </button>

    <button class="filter_btn" data-filter="created" aria-pressed="false">
        Created
    </button>
</div>

<div class="section_container">

    <?php foreach ($quizzes as $q): ?>

        <article class="section" data-type="<?= htmlspecialchars($q['type']) ?>">

            <div class="section_content">

                <h2>
                    <?= htmlspecialchars($q['title']) ?>
                </h2>

                <p>
                    <?= htmlspecialchars($q['description'] ?? '') ?>
                </p>

                <div class="section_actions">
                    <a
                        href="quiz.php?id=<?= (int)$q['id'] ?>"
                        class="section_play_btn">

                        QUIZZY!

                    </a>
                </div>

            </div>

            <?php if (!empty($q['image'])): ?>
                <img
                    class="section_img"
                    src="<?= htmlspecialchars($q['image']) ?>"
                    alt="<?= htmlspecialchars($q['title']) ?>">
            <?php endif; ?>

        </article>

    <?php endforeach; ?>

    <?php if (!$quizzes): ?>
        <p>No quizzes yet.</p>
    <?php endif; ?>

</div>

<script>
// Filter buttons: All / Official / Created
document.querySelectorAll('.filter_btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var filter = btn.dataset.filter;

        document.querySelectorAll('.filter_btn').forEach(function (b) {
            b.classList.toggle('active', b === btn);
            b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
        });

        document.querySelectorAll('.section').forEach(function (card) {
            card.style.display =
                (filter === 'all' || card.dataset.type === filter) ? '' : 'none';
        });
    });
});
</script>

</body>
</html>