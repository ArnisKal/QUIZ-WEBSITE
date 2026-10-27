<?php
require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../theme.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* пустую строку превращаем в NULL */
function nn(?string $v): ?string {
    $v = trim($v ?? '');
    return $v === '' ? null : $v;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Bad token');
    }

    try {
        $pdo->beginTransaction();

        $t = $pdo->prepare('UPDATE site_texts SET text_value = ?, text_value_lv = ? WHERE text_key = ?');
        foreach (['hero_title', 'hero_text'] as $key) {
            $t->execute([
                trim($_POST[$key] ?? ''),
                nn($_POST[$key . '_lv'] ?? ''),
                $key,
            ]);
        }

        $b = $pdo->prepare('UPDATE info_blocks SET image = ?, title = ?, body = ?, title_lv = ?, body_lv = ? WHERE id = ?');
        foreach ($_POST['blocks'] ?? [] as $id => $row) {
            $b->execute([
                nn($row['image'] ?? ''),
                trim($row['title'] ?? ''),
                trim($row['body'] ?? ''),
                nn($row['title_lv'] ?? ''),
                nn($row['body_lv'] ?? ''),
                (int)$id,
            ]);
        }

        $f = $pdo->prepare('UPDATE faq_items SET question = ?, answer = ?, question_lv = ?, answer_lv = ? WHERE id = ?');
        foreach ($_POST['faq'] ?? [] as $id => $row) {
            $f->execute([
                trim($row['question'] ?? ''),
                trim($row['answer'] ?? ''),
                nn($row['question_lv'] ?? ''),
                nn($row['answer_lv'] ?? ''),
                (int)$id,
            ]);
        }

        $pdo->commit();
        $_SESSION['flash'] = 'Homepage content saved.';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = 'Could not save. Check that the LV columns exist in the database.';
    }

    header('Location: content.php');
    exit;
}

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

/* texts: key => вся строка (там и text_value, и text_value_lv) */
$texts = [];
foreach ($pdo->query('SELECT * FROM site_texts') as $row) {
    $texts[$row['text_key']] = $row;
}
$blocks = $pdo->query('SELECT * FROM info_blocks ORDER BY sort_order, id')->fetchAll();
$faq    = $pdo->query('SELECT * FROM faq_items ORDER BY sort_order, id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage content - Quizzity</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<header class="header">
    <a href="../quizes.php" class="logo">QUIZZITY</a>

    <nav class="nav">
        <span>Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'admin') ?></span>
        <a href="admin.php">Quizzes</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<main class="admin_main">

    <div class="admin_top">
        <div>
            <h1>Homepage content</h1>
            <p>Texts and blocks shown on the main page (English and Latvian)</p>
        </div>
        <div class="admin_actions">
            <a href="../index.php" class="btn" target="_blank">View page</a>
            <button type="submit" form="contentForm" class="btn btn_dark">Save</button>
        </div>
    </div>

    <?php if ($message): ?>
        <p class="flash_ok admin_flash" role="status"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" id="contentForm">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <section class="content_card">
            <h2>Welcome</h2>
            <p class="content_hint">The big card at the top of the page. If the LV field is empty, English is shown.</p>

            <div class="form_stack">
                <div class="field">
                    <label for="hero_title">Title (EN)</label>
                    <input id="hero_title" name="hero_title" maxlength="100"
                           value="<?= htmlspecialchars($texts['hero_title']['text_value'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="hero_title_lv">Title (LV)</label>
                    <input id="hero_title_lv" name="hero_title_lv" maxlength="100"
                           value="<?= htmlspecialchars($texts['hero_title']['text_value_lv'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="hero_text">Text (EN)</label>
                    <textarea id="hero_text" name="hero_text" rows="2"><?= htmlspecialchars($texts['hero_text']['text_value'] ?? '') ?></textarea>
                </div>
                <div class="field">
                    <label for="hero_text_lv">Text (LV)</label>
                    <textarea id="hero_text_lv" name="hero_text_lv" rows="2"><?= htmlspecialchars($texts['hero_text']['text_value_lv'] ?? '') ?></textarea>
                </div>
            </div>
        </section>

        <section class="content_card">
            <h2>Info blocks</h2>
            <p class="content_hint">Leave the image empty if the block should have no picture</p>

            <div class="content_grid">
                <?php foreach ($blocks as $i => $b): $id = (int)$b['id']; ?>
                    <div class="content_item">
                        <span class="content_num">Block <?= $i + 1 ?></span>

                        <?php if ($b['image']): ?>
                            <img class="content_thumb" src="../<?= htmlspecialchars($b['image']) ?>" alt="">
                        <?php endif; ?>

                        <div class="field">
                            <label for="b_img_<?= $id ?>">Image path</label>
                            <input id="b_img_<?= $id ?>" name="blocks[<?= $id ?>][image]" maxlength="255"
                                   placeholder="images/example.jpg"
                                   value="<?= htmlspecialchars($b['image'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="b_title_<?= $id ?>">Title (EN)</label>
                            <input id="b_title_<?= $id ?>" name="blocks[<?= $id ?>][title]" maxlength="100" required
                                   value="<?= htmlspecialchars($b['title']) ?>">
                        </div>
                        <div class="field">
                            <label for="b_title_lv_<?= $id ?>">Title (LV)</label>
                            <input id="b_title_lv_<?= $id ?>" name="blocks[<?= $id ?>][title_lv]" maxlength="100"
                                   value="<?= htmlspecialchars($b['title_lv'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="b_body_<?= $id ?>">Text (EN)</label>
                            <textarea id="b_body_<?= $id ?>" name="blocks[<?= $id ?>][body]" rows="3" required><?= htmlspecialchars($b['body']) ?></textarea>
                        </div>
                        <div class="field">
                            <label for="b_body_lv_<?= $id ?>">Text (LV)</label>
                            <textarea id="b_body_lv_<?= $id ?>" name="blocks[<?= $id ?>][body_lv]" rows="3"><?= htmlspecialchars($b['body_lv'] ?? '') ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="content_card">
            <h2>FAQ</h2>
            <p class="content_hint">Questions at the bottom of the page</p>

            <div class="content_list">
                <?php foreach ($faq as $i => $item): $id = (int)$item['id']; ?>
                    <div class="content_item">
                        <span class="content_num">Question <?= $i + 1 ?></span>

                        <div class="field">
                            <label for="f_q_<?= $id ?>">Question (EN)</label>
                            <input id="f_q_<?= $id ?>" name="faq[<?= $id ?>][question]" maxlength="255" required
                                   value="<?= htmlspecialchars($item['question']) ?>">
                        </div>
                        <div class="field">
                            <label for="f_q_lv_<?= $id ?>">Question (LV)</label>
                            <input id="f_q_lv_<?= $id ?>" name="faq[<?= $id ?>][question_lv]" maxlength="255"
                                   value="<?= htmlspecialchars($item['question_lv'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="f_a_<?= $id ?>">Answer (EN)</label>
                            <textarea id="f_a_<?= $id ?>" name="faq[<?= $id ?>][answer]" rows="3" required><?= htmlspecialchars($item['answer']) ?></textarea>
                        </div>
                        <div class="field">
                            <label for="f_a_lv_<?= $id ?>">Answer (LV)</label>
                            <textarea id="f_a_lv_<?= $id ?>" name="faq[<?= $id ?>][answer_lv]" rows="3"><?= htmlspecialchars($item['answer_lv'] ?? '') ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="form_actions">
            <button type="submit" class="btn btn_dark">Save changes</button>
        </div>
    </form>

</main>

</body>
</html>