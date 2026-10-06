<?php
require_once 'admin/admin_check.php';

$error = '';
$title = '';
$description = '';
$image = '';
$type = 'official';

if($_SERVER['REQUEST_METHOD']=== 'POST'){
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image       = trim($_POST['image'] ?? '');
    $type        = ($_POST['type'] ?? '') === 'created' ? 'created' : 'official';

    if ($title === '') {
        $error = 'Please enter a quiz title';
    } elseif (mb_strlen($title) > 100) {
        $error = 'Title must be 100 characters or less';
    } else {
        // 3. Save to the database
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

        // 4. Go to the edit page to add questions
        header('Location: quiz_edit.php?id=' . $pdo->lastInsertId());
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
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<h1>New Quiz</h1>
<p><a href="admin/admin.php">Back to admin panel</a></p>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <p>
        <label>Title<br>
            <input name="title" maxlength="100" required
                   value="<?= htmlspecialchars($title) ?>">
        </label>
    </p>
    <p>
        <label>Description<br>
            <textarea name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
        </label>
    </p>
    <p>
        <label>Image path (optional)<br>
            <input name="image" placeholder="images/pic.png"
                   value="<?= htmlspecialchars($image) ?>">
        </label>
    </p>
    <p>
        <label>Type<br>
            <select name="type">
                <option value="official" <?= $type === 'official' ? 'selected' : '' ?>>Official</option>
                <option value="created"  <?= $type === 'created'  ? 'selected' : '' ?>>Created</option>
            </select>
        </label>
    </p>
    <button type="submit">Create</button>
</form>

</body>
</html>