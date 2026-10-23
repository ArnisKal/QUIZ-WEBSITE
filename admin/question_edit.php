<?php
require_once 'admin_check.php';

const MIN_ANSWERS = 2;
const MAX_ANSWERS = 8;
const MIN_TIME    = 10;
const MAX_TIME    = 180;
const IMAGES_DIR  = __DIR__ . '/../images';
const IMAGES_URL  = '../images';

$quizId = (int)($_GET['quiz_id'] ?? 0);
$id     = (int)($_GET['id'] ?? 0);
$editing = $id > 0;

$stmt = $pdo->prepare('SELECT id, title FROM quizzes WHERE id = ?');
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();
if (!$quiz) {
    http_response_code(404);
    exit('Quiz Not Found!');
}
$images = [];
if (is_dir(IMAGES_DIR)) {
    foreach (scandir(IMAGES_DIR) as $f) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $images[] = $f;
        }
    }
    sort($images);
}

$text      = '';
$image     = '';
$timeLimit = 30;
$options   = ['', '', '', ''];
$correct   = '';

if ($editing) {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE id = ? AND quiz_id = ?');
    $stmt->execute([$id, $quizId]);
    $question = $stmt->fetch();
    if (!$question) {
        http_response_code(404);
        exit('Question not found');
    }
    $text      = $question['question_text'];
    $image     = (string)($question['image'] ?? '');
    $timeLimit = (int)$question['time_limit'];

    $stmt = $pdo->prepare('SELECT id, answer_text, is_correct FROM answers WHERE question_id = ? ORDER BY id');
    $stmt->execute([$id]);
    $options = [];
    foreach ($stmt->fetchAll() as $a) {
        $options[(int)$a['id']] = $a['answer_text'];
        if ($a['is_correct']) { $correct = (string)$a['id']; }
    }
    if ($correct === '' && $options) { $correct = (string)array_key_first($options); }
}

$count = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE quiz_id = ?');
$count->execute([$quizId]);
$total = (int)$count->fetchColumn();

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text      = trim($_POST['question_text'] ?? '');
    $image     = (string)($_POST['image'] ?? '');
    $timeLimit = (int)($_POST['time_limit'] ?? 0);
    $posted    = is_array($_POST['answers'] ?? null) ? array_filter($_POST['answers'], 'is_string') : [];
    $correct   = (string)($_POST['correct'] ?? '');
    $options   = $posted;

    if ($text === '') {
        $error = 'Please enter the question text';
    } elseif ($timeLimit < MIN_TIME || $timeLimit > MAX_TIME) {
        $error = 'Timer must be between ' . MIN_TIME . ' and ' . MAX_TIME . ' seconds';
    } elseif ($image !== '' && !in_array($image, $images, true)) {
        $error = 'Invalid image';
    } elseif ($editing) {
        $stmt = $pdo->prepare('SELECT id FROM answers WHERE question_id = ?');
        $stmt->execute([$id]);
        $existingIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        $hasEmpty = false;
        $invalid  = false;
        foreach ($posted as $key => $t) {
            if (trim($t) === '') { $hasEmpty = true; }
            $isExisting = ctype_digit((string)$key) && in_array((int)$key, $existingIds, true);
            $isNew      = preg_match('/^new_[0-9]+$/', (string)$key) === 1;
            if (!$isExisting && !$isNew) { $invalid = true; }
        }

        if ($invalid) {
            $error = 'Invalid form data';
        } elseif ($hasEmpty) {
            $error = 'Answer options cannot be empty (fill them in or remove them)';
        } elseif (count($posted) < MIN_ANSWERS) {
            $error = 'At least ' . MIN_ANSWERS . ' answer options are required';
        } elseif (count($posted) > MAX_ANSWERS) {
            $error = 'No more than ' . MAX_ANSWERS . ' answer options';
        } elseif (!array_key_exists($correct, $posted)) {
            $error = 'Please select the correct answer';
        } else {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE questions SET question_text = ?, image = ?, time_limit = ? WHERE id = ?')
                    ->execute([$text, $image !== '' ? $image : null, $timeLimit, $id]);

                $upd = $pdo->prepare('UPDATE answers SET answer_text = ?, is_correct = ? WHERE id = ? AND question_id = ?');
                $ins = $pdo->prepare('INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)');
                $keep = [];

                foreach ($posted as $key => $answerText) {
                    $isCorrect = ((string)$key === $correct) ? 1 : 0;
                    if (ctype_digit((string)$key)) {
                        $upd->execute([trim($answerText), $isCorrect, (int)$key, $id]);
                        $keep[] = (int)$key;
                    } else {
                        $ins->execute([$id, trim($answerText), $isCorrect]);
                    }
                }

                $del = $pdo->prepare('DELETE FROM answers WHERE id = ? AND question_id = ?');
                foreach (array_diff($existingIds, $keep) as $removedId) {
                    $del->execute([$removedId, $id]);
                }

                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            $_SESSION['flash'] = 'Question updated';
            header("Location: quiz_edit.php?id=$quizId");
            exit;
        }
    } else {
        $filled = array_filter($posted, fn($a) => trim($a) !== '');

        if (count($filled) < MIN_ANSWERS) {
            $error = 'At least ' . MIN_ANSWERS . ' answer options are required';
        } elseif (count($filled) > MAX_ANSWERS) {
            $error = 'No more than ' . MAX_ANSWERS . ' answer options';
        } elseif (!isset($filled[$correct])) {
            $error = 'The correct answer cannot be empty';
        } else {
            $pdo->beginTransaction();
            try {
                $next = $pdo->prepare('SELECT COALESCE(MAX(question_number), 0) + 1 FROM questions WHERE quiz_id = ?');
                $next->execute([$quizId]);
                $number = (int)$next->fetchColumn();

                $pdo->prepare('INSERT INTO questions (quiz_id, question_text, question_number, image, time_limit) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$quizId, $text, $number, $image !== '' ? $image : null, $timeLimit]);
                $questionId = $pdo->lastInsertId();

                $ins = $pdo->prepare('INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)');
                foreach ($filled as $i => $answerText) {
                    $ins->execute([$questionId, trim($answerText), (string)$i === $correct ? 1 : 0]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }

            if (isset($_POST['save_next'])) {
                $_SESSION['flash'] = 'Question ' . $number . ' saved. Add the next one.';
                header("Location: question_edit.php?quiz_id=$quizId");
            } else {
                $_SESSION['flash'] = 'Question added';
                header("Location: quiz_edit.php?id=$quizId");
            }
            exit;
        }
    }
}
if (!$editing) {
    $nextKey = $options ? max(array_map('intval', array_keys($options))) + 1 : 0;
    while (count($options) < 4) {
        $options[$nextKey++] = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Edit question' : 'Add question' ?> - <?= htmlspecialchars($quiz['title']) ?></title>
    <link rel="stylesheet" href="../styles.css">
    <script>
    try {
        if (localStorage.getItem('theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    } catch (e) {}
    </script>
</head>
<body>
<div class="admin_main">

    <div class="admin_top">
        <div>
            <h1><?= $editing ? 'Edit question' : 'Question ' . ($total + 1) ?></h1>
            <p><?= htmlspecialchars($quiz['title']) ?></p>
        </div>
        <a href="quiz_edit.php?id=<?= $quizId ?>" class="btn">Back to quiz</a>
    </div>

    <?php if ($message): ?><p class="flash_ok"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="form_error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post" class="form_card form_stack">
        <div class="field">
            <label for="question_text">Question</label>
            <input id="question_text" name="question_text" value="<?= htmlspecialchars($text) ?>" required autofocus>
        </div>

        <div class="field">
            <label for="time_limit">Timer <span class="field_hint">(seconds, <?= MIN_TIME ?>–<?= MAX_TIME ?>)</span></label>
            <input id="time_limit" name="time_limit" type="number"
                   min="<?= MIN_TIME ?>" max="<?= MAX_TIME ?>" step="1"
                   value="<?= (int)$timeLimit ?>" required>
        </div>

        <div class="field">
            <label for="image">Image <span class="field_hint">(optional)</span></label>
            <select id="image" name="image">
                <option value="">— No image —</option>
                <?php foreach ($images as $img): ?>
                    <option value="<?= htmlspecialchars($img) ?>" <?= $img === $image ? 'selected' : '' ?>>
                        <?= htmlspecialchars($img) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <img id="imagePreview" alt="" style="display:none; max-width:240px; margin-top:.6rem; border-radius:6px">
        </div>

        <div class="field">
            <label>Answer options <span class="field_hint">(select the correct one, <?= MIN_ANSWERS ?>–<?= MAX_ANSWERS ?> options)</span></label>
            <div class="answers_list" id="answersList">
                <?php $n = 0; foreach ($options as $key => $optText): $n++; ?>
                    <div class="answer_row">
                        <input type="radio" name="correct" value="<?= htmlspecialchars((string)$key) ?>"
                               <?= (string)$key === $correct ? 'checked' : '' ?> aria-label="Correct answer">
                        <input type="text" name="answers[<?= htmlspecialchars((string)$key) ?>]"
                               value="<?= htmlspecialchars($optText) ?>"
                               placeholder="Option <?= $n ?>"
                               <?= $editing ? 'required' : '' ?>>
                        <button type="button" class="btn btn_danger remove_btn" aria-label="Remove option">✕</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="btn" id="addBtn" style="margin-top:.6rem">+ Add answer</button>
        </div>

        <div class="form_actions">
            <?php if ($editing): ?>
                <button type="submit" class="btn btn_dark">Save question</button>
            <?php else: ?>
                <button type="submit" class="btn">Save and finish</button>
                <button type="submit" name="save_next" value="1" class="btn btn_dark">Save and add next</button>
            <?php endif; ?>
        </div>
    </form>

</div>

<script>
var MIN = <?= MIN_ANSWERS ?>;
var MAX = <?= MAX_ANSWERS ?>;
var EDITING = <?= $editing ? 'true' : 'false' ?>;
var list   = document.getElementById('answersList');
var addBtn = document.getElementById('addBtn');
var newCounter = 0;

function refresh() {
    var rows = list.querySelectorAll('.answer_row');

    rows.forEach(function (row, i) {
        row.querySelector('input[type="text"]').placeholder = 'Option ' + (i + 1);
        row.querySelector('.remove_btn').disabled = rows.length <= MIN;
    });

    addBtn.style.display = rows.length >= MAX ? 'none' : 'inline-block';

    if (!list.querySelector('input[type="radio"]:checked') && rows.length) {
        rows[0].querySelector('input[type="radio"]').checked = true;
    }
}

addBtn.addEventListener('click', function () {
    if (list.querySelectorAll('.answer_row').length >= MAX) return;

    var key = 'new_' + Date.now() + (newCounter++);
    var row = document.createElement('div');
    row.className = 'answer_row';
    row.innerHTML =
        '<input type="radio" name="correct" value="' + key + '" aria-label="Correct answer">' +
        '<input type="text" name="answers[' + key + ']"' + (EDITING ? ' required' : '') + '>' +
        '<button type="button" class="btn btn_danger remove_btn" aria-label="Remove option">✕</button>';

    list.appendChild(row);
    refresh();
    row.querySelector('input[type="text"]').focus();
});

list.addEventListener('click', function (e) {
    if (!e.target.classList.contains('remove_btn')) return;
    if (list.querySelectorAll('.answer_row').length <= MIN) return;
    e.target.closest('.answer_row').remove();
    refresh();
});
var IMAGES_URL = '<?= IMAGES_URL ?>/';
var imageSelect  = document.getElementById('image');
var imagePreview = document.getElementById('imagePreview');

function updatePreview() {
    if (imageSelect.value) {
        imagePreview.src = IMAGES_URL + encodeURIComponent(imageSelect.value);
        imagePreview.style.display = 'block';
    } else {
        imagePreview.style.display = 'none';
    }
}
imageSelect.addEventListener('change', updatePreview);
updatePreview();

refresh();
</script>

<script src="../toast.js"></script>
</body>
</html>