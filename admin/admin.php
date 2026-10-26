<?php
require_once __DIR__ . '/admin_check.php';

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

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
    <title>Quizzes - Quizzity</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
     <script>
        try {
            if (localStorage.getItem('theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        } catch (e) {}
    </script>
</head>
<body>

<header class="header">
    <a href="../quizes.php" class="logo">QUIZZITY</a>

    <nav class="nav">
        <span>Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'admin') ?></span>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<main class="admin_main">

    <div class="admin_top">
        <div>
            <h1>Quizzes</h1>
            <p id="quizCount" data-total="<?= count($quizzes) ?>"><?= count($quizzes) ?> total</p>
        </div>
        <a href="../quiz_create.php" class="btn btn_dark">New quiz</a>
    </div>

    <?php if ($message): ?>
        <p class="flash_ok admin_flash" role="status"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($quizzes): ?>
        <div class="admin_toolbar">
            <input type="search" id="quizSearch" class="search" placeholder="Search by title"
                   aria-label="Search quizzes" autocomplete="off">

            <div class="filters" role="group" aria-label="Filter by type">
                <button type="button" class="filter_btn active" data-type="all" aria-pressed="true">All</button>
                <button type="button" class="filter_btn" data-type="official" aria-pressed="false">Official</button>
                <button type="button" class="filter_btn" data-type="created" aria-pressed="false">Created</button>
            </div>
        </div>
    <?php endif; ?>

    <div class="admin_card">
        <?php if (!$quizzes): ?>

            <div class="admin_empty">
                <p>No quizzes yet.</p>
                <a href="../quiz_create.php" class="btn btn_dark">Create the first quiz</a>
            </div>

        <?php else: ?>

            <table class="admin_table">
                <thead>
                    <tr>
                        <th class="col_id">ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Questions</th>
                        <th class="col_actions"><span class="sr_only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $q): ?>
                        <tr data-title="<?= htmlspecialchars(mb_strtolower($q['title'])) ?>"
                            data-type="<?= htmlspecialchars($q['type']) ?>">
                            <td class="col_id"><?= (int)$q['id'] ?></td>
                            <td class="col_title"><?= htmlspecialchars($q['title']) ?></td>
                            <td>
                                <span class="tag tag_<?= htmlspecialchars($q['type']) ?>">
                                    <?= htmlspecialchars(ucfirst($q['type'])) ?>
                                </span>
                            </td>
                            <td<?= (int)$q['questions_count'] === 0 ? ' class="count_zero"' : '' ?>>
                                <?= (int)$q['questions_count'] ?>
                            </td>
                            <td class="col_actions">
                                <div class="admin_actions">
                                    <a class="btn" href="quiz_edit.php?id=<?= (int)$q['id'] ?>">Edit</a>
                                    <form method="post" action="quiz_delete.php"
                                          data-confirm="<?= htmlspecialchars('Delete "' . $q['title'] . '"? This cannot be undone.') ?>">
                                        <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
                                        <button type="submit" class="btn btn_danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>


            <p class="admin_empty" id="noResults" hidden>No quizzes match your search.</p>

        <?php endif; ?>
    </div>

    <br>

     <div class="admin_actions">
        <a href="content.php" class="btn">Homepage content</a>
     </div>

</main>

<?php if ($quizzes): ?>
<script>
    const rows      = [...document.querySelectorAll('.admin_table tbody tr')];
    const search    = document.getElementById('quizSearch');
    const filters   = [...document.querySelectorAll('.filter_btn')];
    const noResults = document.getElementById('noResults');
    const counter   = document.getElementById('quizCount');
    const total     = Number(counter.dataset.total);

    let type = 'all';

    function applyFilters() {
        const query = search.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach(row => {
            const show = (type === 'all' || row.dataset.type === type)
                      && row.dataset.title.includes(query);
            row.hidden = !show;
            if (show) visible++;
        });

        noResults.hidden = visible > 0;
        counter.textContent = visible === total ? total + ' total' : visible + ' of ' + total;
    }

    search.addEventListener('input', applyFilters);

    filters.forEach(btn => btn.addEventListener('click', () => {
        type = btn.dataset.type;
        filters.forEach(b => {
            const on = b === btn;
            b.classList.toggle('active', on);
            b.setAttribute('aria-pressed', on);
        });
        applyFilters();
    }));

    /* confirm before deleting */
    document.addEventListener('submit', e => {
        const message = e.target.dataset.confirm;
        if (message && !confirm(message)) e.preventDefault();
    });
</script>
<?php endif; ?>

</body>
</html>