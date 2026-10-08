<?php
require_once 'admin_check.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id FROM quizzes WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    exit('Quiz Not Found!');
}

function renumberQuestions(PDO $pdo, int $quizId): void {
    $stmt = $pdo->prepare('SELECT id FROM questions WHERE quiz_id = ? ORDER BY question_number, id');
    $stmt->execute([$quizId]);
    $update = $pdo->prepare('UPDATE questions SET question_number = ? WHERE id = ?');
    $n = 1;
    foreach ($stmt->fetchAll() as $row) {
        $update->execute([$n++, $row['id']]);
    }
}

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_quiz') {
        $title = trim($_POST['title'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $type  = ($_POST['type'] ?? '') === 'created' ? 'created' : 'official';

        if ($title === '') {
            $error = 'Title cannot be empty';
        } elseif (mb_strlen($title) > 100) {
            $error = 'Title must be 100 characters or less';
        } else {
            $pdo->prepare("UPDATE quizzes SET title = ?, description = ?, image = ?, type = ? WHERE id = ?")
                ->execute([$title, trim($_POST['description'] ?? ''), $image !== '' ? $image : null, $type, $id]);
            $_SESSION['flash'] = 'Quiz saved';
            header("Location: quiz_edit.php?id=$id");
            exit;
        }
    }

    if ($action === 'delete_question') {
        $questionId = (int)($_POST['question_id'] ?? 0);
        $pdo->prepare("DELETE FROM questions WHERE id = ? AND quiz_id = ?")->execute([$questionId, $id]);
        renumberQuestions($pdo, $id);
        $_SESSION['flash'] = 'Question deleted';
        header("Location: quiz_edit.php?id=$id");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

$stmt = $pdo->prepare(
    "SELECT q.*, (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) AS answers_count
     FROM questions q WHERE q.quiz_id = ? ORDER BY q.question_number, q.id"
);
$stmt->execute([$id]);
$questions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editing: <?= htmlspecialchars($quiz['title']) ?></title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="admin_main">

    <div class="admin_top">
        <h1>Edit quiz</h1>
        <a href="admin.php" class="btn">Back to quiz list</a>
    </div>

    <?php if ($message): ?><p class="flash_ok"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="form_error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post" class="form_card form_stack">
        <input type="hidden" name="action" value="update_quiz">

        <div class="field">
            <label for="title">Title</label>
            <input id="title" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" maxlength="100" required>
        </div>
        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($quiz['description'] ?? '') ?></textarea>
        </div>
        <div class="field">
            <label for="image">Image <span class="field_hint">(path, e.g. images/pic.png)</span></label>
            <input id="image" name="image" value="<?= htmlspecialchars($quiz['image'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="type">Type</label>
            <select id="type" name="type">
                <option value="official" <?= $quiz['type'] === 'official' ? 'selected' : '' ?>>Official</option>
                <option value="created"  <?= $quiz['type'] === 'created'  ? 'selected' : '' ?>>Created</option>
            </select>
        </div>
        <div class="form_actions">
            <button type="submit" class="btn btn_dark">Save quiz</button>
        </div>
    </form>

    <div class="admin_top" style="margin-top:2rem">
        <h2>Questions (<?= count($questions) ?>)</h2>
        <a href="question_edit.php?quiz_id=<?= $id ?>" class="btn btn_play">+ Add question</a>
    </div>

    <?php if (!$questions): ?>
        <div class="admin_card"><p class="admin_empty">No questions yet. Add the first one.</p></div>
    <?php else: ?>
        <div class="admin_card">
            <table class="admin_table">
                <thead>
                    <tr><th class="col_id">#</th><th>Question</th><th>Answers</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($questions as $n => $q): ?>
                    <tr>
                        <td class="col_id"><?= $n + 1 ?></td>
                        <td class="col_title"><?= htmlspecialchars(mb_strimwidth($q['question_text'], 0, 80, '...')) ?></td>
                        <td><?= (int)$q['answers_count'] ?></td>
                        <td class="col_actions">
                            <div class="admin_actions">
                                <a href="question_edit.php?quiz_id=<?= $id ?>&amp;id=<?= (int)$q['id'] ?>" class="btn">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete this question?');">
                                    <input type="hidden" name="action" value="delete_question">
                                    <input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">
                                    <button type="submit" class="btn btn_danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>
</body>
</html>