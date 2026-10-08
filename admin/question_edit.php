<?php
require_once 'admin_check.php';

$quizId = (int)($_GET['quiz_id'] ?? 0);
$id     = (int)($_GET['id'] ?? 0);
$editing = $id > 0;

$stmt = $pdo->prepare('SELECT id, title FROM quizzes WHERE id = ?');
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();
if (!$quiz) {
    exit('Quiz Not Found!');
}

$text    = '';
$options = ['', '', '', ''];   
$correct = 0;

if ($editing) {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE id = ? AND quiz_id = ?');
    $stmt->execute([$id, $quizId]);
    $question = $stmt->fetch();
    if (!$question) {
        exit('Question not found');
    }
    $text = $question['question_text'];

    $stmt = $pdo->prepare('SELECT id, answer_text, is_correct FROM answers WHERE question_id = ? ORDER BY id');
    $stmt->execute([$id]);
    $options = [];
    foreach ($stmt->fetchAll() as $a) {
        $options[(int)$a['id']] = $a['answer_text'];
        if ($a['is_correct']) { $correct = (int)$a['id']; }
    }
    if (!$correct && $options) { $correct = array_key_first($options); }
}

$count = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE quiz_id = ?');
$count->execute([$quizId]);
$total = (int)$count->fetchColumn();

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text    = trim($_POST['question_text'] ?? '');
    $posted  = is_array($_POST['answers'] ?? null) ? array_filter($_POST['answers'], 'is_string') : [];
    $correct = (int)($_POST['correct'] ?? 0);
    $options = $posted;                     

    if ($text === '') {
        $error = 'Please enter the question text';
    } elseif ($editing) {
        $hasEmpty = false;
        foreach ($posted as $t) { if (trim($t) === '') { $hasEmpty = true; } }

        if ($hasEmpty) {
            $error = 'Answer options cannot be empty';
        } elseif (!array_key_exists($correct, $posted)) {
            $error = 'Please select the correct answer';
        } else {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE questions SET question_text = ? WHERE id = ?')->execute([$text, $id]);
                $upd = $pdo->prepare('UPDATE answers SET answer_text = ?, is_correct = ? WHERE id = ? AND question_id = ?');
                foreach ($posted as $answerId => $answerText) {
                    $answerId = (int)$answerId;
                    $upd->execute([trim($answerText), $answerId === $correct ? 1 : 0, $answerId, $id]);
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

        if (count($filled) < 2) {
            $error = 'At least 2 answer options are required';
        } elseif (!isset($filled[$correct])) {
            $error = 'The correct answer cannot be empty';
        } else {
            $pdo->beginTransaction();
            try {
                $next = $pdo->prepare('SELECT COALESCE(MAX(question_number), 0) + 1 FROM questions WHERE quiz_id = ?');
                $next->execute([$quizId]);
                $number = (int)$next->fetchColumn();

                $pdo->prepare('INSERT INTO questions (quiz_id, question_text, question_number) VALUES (?, ?, ?)')
                    ->execute([$quizId, $text, $number]);
                $questionId = $pdo->lastInsertId();

                $ins = $pdo->prepare('INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)');
                foreach ($filled as $i => $answerText) {
                    $ins->execute([$questionId, trim($answerText), (int)$i === $correct ? 1 : 0]);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Edit question' : 'Add question' ?> - <?= htmlspecialchars($quiz['title']) ?></title>
    <link rel="stylesheet" href="../styles.css">
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
            <label>Answer options <span class="field_hint">(select the correct one)</span></label>
            <div class="answers_list">
                <?php foreach ($options as $key => $optText): ?>
                    <div class="answer_row">
                        <input type="radio" name="correct" value="<?= (int)$key ?>"
                               <?= (int)$key === $correct ? 'checked' : '' ?> aria-label="Correct answer">
                        <input type="text" name="answers[<?= (int)$key ?>]"
                               value="<?= htmlspecialchars($optText) ?>"
                               placeholder="Option <?= (int)array_search($key, array_keys($options)) + 1 ?>"
                               <?= $editing ? 'required' : '' ?>>
                    </div>
                <?php endforeach; ?>
            </div>
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
</body>
</html>