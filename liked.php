<?php
session_start();

// Only for logged-in users
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: admin/admin.php');
    exit;
}
require_once 'site_check.php';
require_once 'database.php';

$stmt = $pdo->prepare("
    SELECT q.*
    FROM quizzes q
    JOIN favourites f ON f.quiz_id = q.id
    WHERE f.user_id = ?
      AND EXISTS (SELECT 1 FROM questions qs WHERE qs.quiz_id = q.id)
    ORDER BY f.created_at DESC
");

$stmt->execute([$_SESSION['user_id']]);
$quizzes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liked quizzes - Quizzity</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        try {
            if (localStorage.getItem('theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        } catch (e) {}
    </script>
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">QUIZZITY</a>

    <form action="search.php" method="get" class="search_form">
        <input type="search" name="q" class="search" placeholder="Search quizzes..." aria-label="Search quizzes">
    </form>

    <nav class="nav">
        <a href="quizes.php">Quizzes</a>
        <a href="leaderboards.php">Leaderboards</a>
        <span class="welcome">Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h1>Liked quizzes</h1>
</main>

<div class="section_container">

    <?php foreach ($quizzes as $q): ?>

        <article class="section" data-type="<?= htmlspecialchars($q['type']) ?>">

            <div class="section_content">
                <h2><?= htmlspecialchars($q['title']) ?></h2>
                <p><?= htmlspecialchars($q['description'] ?? '') ?></p>

                <div class="section_actions">
                    <a href="quiz.php?id=<?= (int)$q['id'] ?>" class="section_play_btn">QUIZZY!</a>

                    <div class="favourite">
                        <button type="button"
                                class="fav_btn active"
                                data-quiz-id="<?= (int)$q['id'] ?>"
                                aria-label="Remove from favourites">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <?php if (!empty($q['image'])): ?>
                <img class="section_img"
                     src="<?= htmlspecialchars($q['image']) ?>"
                     alt="<?= htmlspecialchars($q['title']) ?>">
            <?php endif; ?>

        </article>

    <?php endforeach; ?>

    <?php if (!$quizzes): ?>
        <p>You haven't liked any quizzes yet. <a href="quizes.php">Browse quizzes</a></p>
    <?php endif; ?>

</div>

<script>
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
                return res.text().then(function (text) {
                    try { return JSON.parse(text); }
                    catch (e) { throw new Error('Server answered: ' + text); }
                });
            })
            .then(function (json) {
                if (!json) return;
                if (json.error) { alert('Error: ' + json.error); return; }

                btn.classList.toggle('active', json.favourite);
                btn.closest('.section').style.opacity = json.favourite ? '1' : '0.5';
            })
            .catch(function (err) { alert(err.message); });
    });
});
</script>

</body>
</html>