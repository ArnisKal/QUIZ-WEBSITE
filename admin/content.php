<?php
require_once __DIR__ . '/admin_check.php';  

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function save_image(array $file): ?string
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed.');
    if ($file['size'] > 3 * 1024 * 1024) throw new RuntimeException('Image is larger than 3 MB.');

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG, GIF or WEBP images are allowed.');

    $dir = __DIR__ . '/../images/uploads';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Could not save the image.');
    }
    return 'images/uploads/' . $name;
}

function delete_uploaded_image(?string $path): void
{
    if ($path && strpos($path, 'images/uploads/') === 0) {
        $full = __DIR__ . '/../' . $path;
        if (is_file($full)) unlink($full);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
            throw new RuntimeException('Invalid form token. Reload the page and try again.');
        }

        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);
        $order  = (int)($_POST['sort_order'] ?? 0);

        switch ($action) {
            case 'faq_add':
            case 'faq_update':
                $q = trim($_POST['question'] ?? '');
                $a = trim($_POST['answer'] ?? '');
                if ($q === '' || $a === '') throw new RuntimeException('Question and answer are required.');
                if ($action === 'faq_add') {
                    $pdo->prepare('INSERT INTO faq_items (question, answer, sort_order) VALUES (?, ?, ?)')
                        ->execute([$q, $a, $order]);
                    $_SESSION['flash'] = 'FAQ item added.';
                } else {
                    $pdo->prepare('UPDATE faq_items SET question = ?, answer = ?, sort_order = ? WHERE id = ?')
                        ->execute([$q, $a, $order, $id]);
                    $_SESSION['flash'] = 'FAQ item saved.';
                }
                break;

            case 'faq_delete':
                $pdo->prepare('DELETE FROM faq_items WHERE id = ?')->execute([$id]);
                $_SESSION['flash'] = 'FAQ item deleted.';
                break;

            case 'block_add':
                $t = trim($_POST['title'] ?? '');
                $d = trim($_POST['description'] ?? '');
                if ($t === '' || $d === '') throw new RuntimeException('Title and description are required.');
                $img = save_image($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
                $pdo->prepare('INSERT INTO info_blocks (title, description, image, sort_order) VALUES (?, ?, ?, ?)')
                    ->execute([$t, $d, $img, $order]);
                $_SESSION['flash'] = 'Feature block added.';
                break;

            case 'block_update':
                $t = trim($_POST['title'] ?? '');
                $d = trim($_POST['description'] ?? '');
                if ($t === '' || $d === '') throw new RuntimeException('Title and description are required.');

                $old = $pdo->prepare('SELECT image FROM info_blocks WHERE id = ?');
                $old->execute([$id]);
                $oldImage = $old->fetchColumn() ?: null;

                $newImage = save_image($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
                if ($newImage !== null) {
                    delete_uploaded_image($oldImage);
                    $image = $newImage;
                } elseif (!empty($_POST['remove_image'])) {
                    delete_uploaded_image($oldImage);
                    $image = null;
                } else {
                    $image = $oldImage;
                }

                $pdo->prepare('UPDATE info_blocks SET title = ?, description = ?, image = ?, sort_order = ? WHERE id = ?')
                    ->execute([$t, $d, $image, $order, $id]);
                $_SESSION['flash'] = 'Feature block saved.';
                break;

            case 'block_delete':
                $old = $pdo->prepare('SELECT image FROM info_blocks WHERE id = ?');
                $old->execute([$id]);
                delete_uploaded_image($old->fetchColumn() ?: null);
                $pdo->prepare('DELETE FROM info_blocks WHERE id = ?')->execute([$id]);
                $_SESSION['flash'] = 'Feature block deleted.';
                break;
        }
    } catch (RuntimeException $e) {
        $_SESSION['flash'] = 'Error: ' . $e->getMessage();
    }

    header('Location: content.php');
    exit;
}

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$blocks = $pdo->query('SELECT * FROM info_blocks ORDER BY sort_order, id')->fetchAll();
$faqs   = $pdo->query('SELECT * FROM faq_items ORDER BY sort_order, id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage content - Quizzity</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <script>
        try {
            if (localStorage.getItem('theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        } catch (e) {}
    </script>
    <style>
        .content_form { padding: 16px 20px; border-bottom: 1px solid rgba(128,128,128,.25); }
        .content_form:last-child { border-bottom: 0; }
        .content_form label { display: block; margin: 10px 0 4px; font-weight: 600; font-size: .9rem; }
        .content_form input[type=text],
        .content_form input[type=number],
        .content_form textarea {
            width: 100%; box-sizing: border-box; padding: 8px 10px; font: inherit;
            color: inherit; background: transparent;
            border: 1px solid rgba(128,128,128,.4); border-radius: 8px;
        }
        .content_form textarea { min-height: 80px; resize: vertical; }
        .content_form input[type=number] { max-width: 120px; }
        .content_form .thumb { display: block; max-width: 160px; max-height: 100px; margin: 6px 0; border-radius: 8px; }
        .content_form .check { display: flex; gap: 6px; align-items: center; font-weight: 400; }
        .content_form .admin_actions { margin-top: 14px; }
        .content_section_title { padding: 16px 20px 0; margin: 0; }
        .content_new { background: rgba(128,128,128,.08); }
    </style>
</head>
<body>

<header class="header">
    <a href="../quizes.php" class="logo">QUIZZITY</a>

    <nav class="nav">
        <span>Hi, <?= h($_SESSION['username'] ?? 'admin') ?></span>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<main class="admin_main">

    <div class="admin_top">
        <div>
            <h1>Homepage content</h1>
            <p>
            <a href="admin.php"> Back to admin page</a>
            </p>
        </div>
    </div>

    <?php if ($message): ?>
        <p class="flash_ok admin_flash" role="status"><?= h($message) ?></p>
    <?php endif; ?>

    <div class="admin_top"><h2>Feature blocks</h2></div>

    <div class="admin_card">
        <?php foreach ($blocks as $b): ?>
            <form class="content_form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">

                <label>Title</label>
                <input type="text" name="title" value="<?= h($b['title']) ?>" maxlength="120" required>

                <label>Description</label>
                <textarea name="description" maxlength="500" required><?= h($b['description']) ?></textarea>

                <label>Image</label>
                <?php if ($b['image']): ?>
                    <img class="thumb" src="../<?= h($b['image']) ?>" alt="">
                    <label class="check"><input type="checkbox" name="remove_image" value="1"> Remove image</label>
                <?php endif; ?>
                <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">

                <label>Order (lower shows first)</label>
                <input type="number" name="sort_order" value="<?= (int)$b['sort_order'] ?>">

                <div class="admin_actions">
                    <button type="submit" name="action" value="block_update" class="btn btn_dark">Save</button>
                    <button type="submit" name="action" value="block_delete" class="btn btn_danger"
                            data-confirm="<?= h('Delete "' . $b['title'] . '"?') ?>">Delete</button>
                </div>
            </form>
        <?php endforeach; ?>

        <form class="content_form content_new" method="post" enctype="multipart/form-data">
            <strong>Add new feature block</strong>
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">

            <label>Title</label>
            <input type="text" name="title" maxlength="120" required>

            <label>Description</label>
            <textarea name="description" maxlength="500" required></textarea>

            <label>Image (optional)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">

            <label>Order</label>
            <input type="number" name="sort_order" value="<?= count($blocks) + 1 ?>">

            <div class="admin_actions">
                <button type="submit" name="action" value="block_add" class="btn btn_dark">Add block</button>
            </div>
        </form>
    </div>

    <div class="admin_top"><h2>FAQ</h2></div>

    <div class="admin_card">
        <?php foreach ($faqs as $f): ?>
            <form class="content_form" method="post">
                <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">

                <label>Question</label>
                <input type="text" name="question" value="<?= h($f['question']) ?>" maxlength="255" required>

                <label>Answer</label>
                <textarea name="answer" required><?= h($f['answer']) ?></textarea>

                <label>Order</label>
                <input type="number" name="sort_order" value="<?= (int)$f['sort_order'] ?>">

                <div class="admin_actions">
                    <button type="submit" name="action" value="faq_update" class="btn btn_dark">Save</button>
                    <button type="submit" name="action" value="faq_delete" class="btn btn_danger"
                            data-confirm="<?= h('Delete this FAQ item?') ?>">Delete</button>
                </div>
            </form>
        <?php endforeach; ?>

        <form class="content_form content_new" method="post">
            <strong>Add new FAQ item</strong>
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">

            <label>Question</label>
            <input type="text" name="question" maxlength="255" required>

            <label>Answer</label>
            <textarea name="answer" required></textarea>

            <label>Order</label>
            <input type="number" name="sort_order" value="<?= count($faqs) + 1 ?>">

            <div class="admin_actions">
                <button type="submit" name="action" value="faq_add" class="btn btn_dark">Add FAQ item</button>
            </div>
        </form>
    </div>

</main>

<script>
    /* confirm before deleting (same pattern as the quizzes page, but on the clicked button) */
    document.addEventListener('click', e => {
        const btn = e.target.closest('button[data-confirm]');
        if (btn && !confirm(btn.dataset.confirm)) e.preventDefault();
    });
</script>

</body>
</html>