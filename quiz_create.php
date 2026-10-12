<?php
require_once 'admin/admin_check.php';

$error = '';
$title = '';
$description = '';
$image = '';
$type = 'official';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image       = trim($_POST['image'] ?? '');
    $type        = ($_POST['type'] ?? '') === 'created' ? 'created' : 'official';

    if ($title === '') {
        $error = 'Please enter a quiz title';
    } elseif (mb_strlen($title) > 100) {
        $error = 'Title must be 100 characters or less';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO quizzes (title, description, image, type, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title,
            $description,
            $image !== '' ? $image : null,
            $type,
            $_SESSION['user_id']
        ]);
        $_SESSION['flash'] = 'Quiz created';
        header('Location: admin/question_edit.php?quiz_id=' . $pdo->lastInsertId());
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <a class="logo" href="index.php">QUIZ</a>
    <nav class="nav">
        <a href="admin/admin.php">Back to admin panel</a>
    </nav>
</header>

<main class="admin_main">
    <div class="admin_top">
        <div>
            <h1>New quiz <span class="admin_badge">Admin</span></h1>
            <p>Fill in the basics. You'll add questions on the next step.</p>
        </div>
    </div>

    <div class="form_card">
        <?php if ($error): ?>
            <p class="form_error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" class="form_stack">
            <div class="field">
                <label for="title">Title</label>
                <input id="title" name="title" maxlength="100" required
                       placeholder="e.g. World capitals"
                       value="<?= htmlspecialchars($title) ?>">
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"
                          placeholder="What is this quiz about?"><?= htmlspecialchars($description) ?></textarea>
            </div>

            <div class="field">
                <label for="image">Image path <span class="field_hint">(optional)</span></label>
                <input id="image" name="image" placeholder="images/pic.png" value="<?= htmlspecialchars($image) ?>">
            </div>

            <div class="field">
                <label for="type">Type</label>
                <select id="type" name="type">
                    <option value="official" <?= $type === 'official' ? 'selected' : '' ?>>Official</option>
                    <option value="created"  <?= $type === 'created'  ? 'selected' : '' ?>>Created</option>
                </select>
            </div>

            <div class="form_actions">
                <a class="btn" href="admin/admin.php">Cancel</a>
                <button type="submit" class="btn btn_dark">Create quiz</button>
            </div>
        </form>
    </div>
</main>

</body>
</html>