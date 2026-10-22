<?php
session_start();

require_once 'site_check.php';
require_once 'database.php';

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

$sql = "
    SELECT q.*
    FROM quizzes q
    WHERE EXISTS (
        SELECT 1
        FROM questions qs
        WHERE qs.quiz_id = q.id
    )
";

if (!$isAdmin) {
    $sql .= " AND q.status = 'public'";
}

$sql .= " ORDER BY q.id DESC";

$quizzes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$favIds = [];

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare(
        'SELECT quiz_id FROM favourites WHERE user_id = ?'
    );

    $stmt->execute([$_SESSION['user_id']]);

    $favIds = array_map(
        'intval',
        $stmt->fetchAll(PDO::FETCH_COLUMN)
    );
}
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

            <a href="account.php" class="avatar"
                title="<?= htmlspecialchars($_SESSION['username']) ?>"
                aria-label="Account: <?= htmlspecialchars($_SESSION['username']) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/>
                    </svg>
            </a>

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

        <?php $isFav = in_array((int)$q['id'], $favIds, true); ?>

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

                    <div class="favourite">
                        <button type="button"
                                class="fav_btn <?= $isFav ? 'active' : '' ?>"
                                data-quiz-id="<?= (int)$q['id'] ?>"
                                aria-label="Add to favourites">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </button>
                    </div>
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

document.querySelectorAll('.fav_btn').forEach(function (btn) {
    btn.addEventListener('click', function () {

        var data = new FormData();
        data.append('quiz_id', btn.dataset.quizId);

        fetch('favourite.php', { method: 'POST', body: data })
            .then(function (res) {
                if (res.status === 401) {       
                    window.location.href = 'login.php';
                    return null;
                }
                return res.json();
            })
            .then(function (json) {
                if (!json) return;
                btn.classList.toggle('active', json.favourite);
            })
            .catch(function (err) { console.error(err); });
    });
});
</script>

</body>
</html>