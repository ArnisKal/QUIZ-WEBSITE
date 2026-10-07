<?php
require_once __DIR__ . '/admin_check.php';

$quizzes = $pdo->query('
    SELECT q.id, q.title, q.type, COUNT(qs.id) AS questions_count
    FROM quizzes q
    LEFT JOIN questions qs ON qs.quiz_id = q.id
    GROUP BY q.id
    ORDER BY q.id DESC
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<header class="header">
    <a href="../quizes.php" class="logo">
        QUIZZITY <span class="admin_badge">Admin</span>
    </a>

    <nav class="nav">
        <span>Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'admin') ?></span>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<main class="admin_main">

    <div class="admin_top">
        <div>
            <h1>Quizzes</h1>
            <p><?= count($quizzes) ?> total</p>
        </div>
        <a href="../quiz_create.php" class="btn btn_dark">+ Create quiz</a>
    </div>

    <div class="admin_card">
        <?php if (!$quizzes): ?>

            <div class="admin_empty">
                <p>No quizzes yet. Create your first one!</p>
            </div>

        <?php else: ?>

            <table class="admin_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Questions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $q): ?>
                        <tr>
                            <td class="col_id"><?= (int)$q['id'] ?></td>
                            <td class="col_title"><?= htmlspecialchars($q['title']) ?></td>
                            <td>
                                <span class="tag tag_<?= htmlspecialchars($q['type']) ?>">
                                    <?= htmlspecialchars(ucfirst($q['type'])) ?>
                                </span>
                            </td>
                            <td class="<?= (int)$q['questions_count'] === 0 ? 'count_zero' : '' ?>">
                                <?= (int)$q['questions_count'] ?>
                            </td>
                            <td class="col_actions">
                                <div class="admin_actions">
                                    <a class="btn btn_play"   href="../quiz.php?id=<?= (int)$q['id'] ?>">Play</a>
                                    <a class="btn"            href="quiz_edit.php?id=<?= (int)$q['id'] ?>">Edit</a>
                                    <a class="btn btn_danger" href="quiz_delete.php?id=<?= (int)$q['id'] ?>">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>

</main>

</body>
</html>