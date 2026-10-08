<?php
session_start();
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: admin/admin.php');
    exit;
}
require_once 'site_check.php';
require_once 'database.php';

$search = trim($_GET['q'] ?? '');
$sql = "
    SELECT q.*
    FROM quizzes q
    WHERE EXISTS (SELECT 1 FROM questions qs WHERE qs.quiz_id = q.id)
";
$params = [];

if ($search !== '') {
    $like = '%' . addcslashes($search, '\\%_') . '%';
    $sql .= " AND (q.title LIKE :t OR q.description LIKE :d)";
    $params[':t'] = $like;
    $params[':d'] = $like;
}

$sql .= " ORDER BY q.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$quizzes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search: <?= htmlspecialchars($search) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">QUIZZITY</a>

    <form action="search.php" method="get" class="search_form">
        <input
            type="search"
            name="q"
            class="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Search quizzes..."
            aria-label="Search quizzes"
        >
    </form>

    <nav class="nav">
        <a href="leaderboards.php">Leaderboards</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="welcome">Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="nav_cta">Sign up</a>
        <?php endif; ?>
    </nav>
</header>

<h2 style="padding: 0 20px;">
    <?php if ($search !== ''): ?>
        Results for “<?= htmlspecialchars($search) ?>” (<?= count($quizzes) ?>)
    <?php else: ?>
        All quizzes
    <?php endif; ?>
</h2>

<div class="filters" role="group" aria-label="Quiz filters">
    <button class="filter_btn active" data-filter="all" aria-pressed="true">All</button>
    <button class="filter_btn" data-filter="official" aria-pressed="false">Official</button>
    <button class="filter_btn" data-filter="created" aria-pressed="false">Created</button>
</div>

<div class="section_container">
    <?php foreach ($quizzes as $quiz): ?>
        <article class="section" data-type="<?= htmlspecialchars($quiz['type']) ?>">
            <div class="section_content">
                <h2><?= htmlspecialchars($quiz['title']) ?></h2>
                <p><?= htmlspecialchars($quiz['description'] ?? '') ?></p>
                <div class="section_actions">
                    <a href="quiz.php?id=<?= (int)$quiz['id'] ?>" class="section_play_btn">QUIZZY!</a>
                </div>
            </div>

            <?php if (!empty($quiz['image'])): ?>
                <img class="section_img"
                     src="<?= htmlspecialchars($quiz['image']) ?>"
                     alt="<?= htmlspecialchars($quiz['title']) ?>">
            <?php endif; ?>
        </article>
    <?php endforeach; ?>

    <?php if (!$quizzes): ?>
        <p>No quizzes found. <a href="index.php">Back to all quizzes</a></p>
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
</script>

</body>
</html>