<?php 
require_once 'admin_check.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo-> prepare('SELECT id FROM quizzes WHERE id = ?');
$stmt->execute([$id]);
if(!$stmt->fetch()){
    exit('Quiz Not Found!');
}

function renumberQuestions(PDO $pdo, int $quizId): void{
    $stmt = $pdo->prepare('SELECT id FROM questions WHERE quiz_id = ? ORDER BY question_number, id');
    $stmt->execute([$quizId]);
    $update = $pdo->prepare('UPDATE questions SET question_number = ? WHERE id = ?');
    $n = 1;
    foreach($stmt->fetchAll()as $row){
    $update->execute([$n++, $row['id']]);
    }
}

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$old = ['text' => '', 'answers' => [], 'correct' => 0];
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
                ->execute([
                    $title,
                    trim($_POST['description'] ?? ''),
                    $image !== '' ? $image : null,
                    $type,
                    $id
                ]);
            $_SESSION['flash'] = 'Quiz saved';
            header("Location: quiz_edit.php?id=$id");
            exit;
        }
    }

    if ($action === 'add_question') {
        $text    = trim($_POST['question_text'] ?? '');
        $answers = $_POST['answers'] ?? [];
        $correct = (int)($_POST['correct'] ?? 0);
        $filled = array_filter($answers, fn($a) => trim($a) !== '');

        if ($text === '') {
            $error = 'Please enter the question text';
        } elseif (count($filled) < 2) {
            $error = 'At least 2 answer options are required';
        } elseif (!isset($filled[$correct])) {
            $error = 'The correct answer cannot be empty';
        } else {
            $pdo->beginTransaction();
            $next = $pdo->prepare("SELECT COALESCE(MAX(question_number), 0) + 1 FROM questions WHERE quiz_id = ?");
            $next->execute([$id]);
            $number = (int)$next->fetchColumn();

            $pdo->prepare("INSERT INTO questions (quiz_id, question_text, question_number) VALUES (?, ?, ?)")
                ->execute([$id, $text, $number]);
            $questionId = $pdo->lastInsertId();

            $ins = $pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            foreach ($filled as $i => $answerText) {
                $ins->execute([$questionId, trim($answerText), (int)$i === $correct ? 1 : 0]);
            }

            $pdo->commit();
            $_SESSION['flash'] = 'Question added';
            header("Location: quiz_edit.php?id=$id");
            exit;
        }
    }


    if ($action === 'update_question') {
        $questionId = (int)($_POST['question_id'] ?? 0);
        $text       = trim($_POST['question_text'] ?? '');
        $answers    = $_POST['answers'] ?? [];        
        $correctId  = (int)($_POST['correct'] ?? 0);
        $check = $pdo->prepare("SELECT id FROM questions WHERE id = ? AND quiz_id = ?");
        $check->execute([$questionId, $id]);

        $hasEmpty = false;
        foreach ($answers as $t) {
            if (trim($t) === '') { $hasEmpty = true; }
        }

        if (!$check->fetch()) {
            $error = 'Question not found';
        } elseif ($text === '') {
            $error = 'Question text cannot be empty';
        } elseif ($hasEmpty) {
            $error = 'Answer options cannot be empty';
        } elseif (!array_key_exists($correctId, $answers)) {
            $error = 'Please select the correct answer';
        } else {
            $pdo->beginTransaction();

            $pdo->prepare("UPDATE questions SET question_text = ? WHERE id = ?")
                ->execute([$text, $questionId]);

            $upd = $pdo->prepare("UPDATE answers SET answer_text = ?, is_correct = ? WHERE id = ? AND question_id = ?");
            foreach ($answers as $answerId => $answerText) {
                $answerId = (int)$answerId;
                $upd->execute([trim($answerText), $answerId === $correctId ? 1 : 0, $answerId, $questionId]);
            }

            $pdo->commit();
            $_SESSION['flash'] = 'Question updated';
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

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_number, id");
$stmt->execute([$id]);
$questions = $stmt->fetchAll();

$answersStmt = $pdo->prepare("SELECT * FROM answers WHERE question_id = ? ORDER BY id");
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

<p><a href="admin.php">← Back to quiz list</a></p>
<h1>Edit Quiz</h1>

<?php if ($message): ?><p style="color:green"><?= htmlspecialchars($message) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<h2>Quiz details</h2>
<form method="post">
    <input type="hidden" name="action" value="update_quiz">
    <p><input name="title" value="<?= htmlspecialchars($quiz['title']) ?>" maxlength="100" required></p>
    <p><textarea name="description" rows="3"><?= htmlspecialchars($quiz['description'] ?? '') ?></textarea></p>
    <p><input name="image" value="<?= htmlspecialchars($quiz['image'] ?? '') ?>" placeholder="images/pic.png"></p>
    <p>
        <select name="type">
            <option value="official" <?= $quiz['type'] === 'official' ? 'selected' : '' ?>>Official</option>
            <option value="created"  <?= $quiz['type'] === 'created'  ? 'selected' : '' ?>>Created</option>
        </select>
    </p>
    <button type="submit">Save quiz</button>
</form>

<h2>Questions (<?= count($questions) ?>)</h2>

<?php foreach ($questions as $n => $q): ?>
    <?php
        $answersStmt->execute([$q['id']]);
        $answers = $answersStmt->fetchAll();
    ?>
    <fieldset style="margin-bottom:20px">
        <legend>Question <?= $n + 1 ?></legend>

        <form method="post">
            <input type="hidden" name="action" value="update_question">
            <input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">

            <p><input name="question_text" value="<?= htmlspecialchars($q['question_text']) ?>" style="width:100%" required></p>

            <?php foreach ($answers as $a): ?>
                <p>
                    <input type="radio" name="correct" value="<?= (int)$a['id'] ?>" <?= $a['is_correct'] ? 'checked' : '' ?>>
                    <input name="answers[<?= (int)$a['id'] ?>]" value="<?= htmlspecialchars($a['answer_text']) ?>" required>
                </p>
            <?php endforeach; ?>

            <button type="submit">Save question</button>
        </form>

        <form method="post" onsubmit="return confirm('Delete this question?');">
            <input type="hidden" name="action" value="delete_question">
            <input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">
            <button type="submit">Delete question</button>
        </form>
    </fieldset>
<?php endforeach; ?>

<h2 id="add-question">Add a question</h2>

<?php if ($error && ($action ?? '') === 'add_question'): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" action="#add-question">
    <input type="hidden" name="action" value="add_question">
    <p>
        <input name="question_text" placeholder="Question text" style="width:100%" required
               value="<?= htmlspecialchars($old['text']) ?>">
    </p>

    <?php for ($i = 0; $i < 4; $i++): ?>
        <p>
            <input type="radio" name="correct" value="<?= $i ?>"
                   <?= $i === (int)$old['correct'] ? 'checked' : '' ?>>
            <input name="answers[<?= $i ?>]" placeholder="Option <?= $i + 1 ?>"
                   value="<?= htmlspecialchars($old['answers'][$i] ?? '') ?>">
        </p>
    <?php endfor; ?>

    <button type="submit">Add question</button>
</form>

</body>
</html>