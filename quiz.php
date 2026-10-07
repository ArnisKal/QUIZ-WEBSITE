<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/site_check.php';   
require_once __DIR__ . '/database.php';

$id = (int)($_GET['id'] ?? 0);

// Quiz
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    http_response_code(404);
    exit('Quiz not found');
}

// Questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_number, id");
$stmt->execute([$id]);
$questions = $stmt->fetchAll();

// Answers for each question
$answersQuestions = [];
$stmt = $pdo->prepare("SELECT id, answer_text, is_correct FROM answers WHERE question_id = ? ORDER BY id");
foreach ($questions as $q) {
    $stmt->execute([$q['id']]);
    $answersQuestions[$q['id']] = $stmt->fetchAll();
}

// Check answers only after the form is submitted
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$chosen = [];
$score = 0;

if ($submitted) {
    foreach ($questions as $q) {
        $qid = (int)$q['id'];
        $chosen[$qid] = (int)($_POST['q'][$qid] ?? 0);

        foreach ($answersQuestions[$qid] as $a) {
            if ((int)$a['id'] === $chosen[$qid] && $a['is_correct']) {
                $score++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?> - Quizzity</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <a href="quizes.php" class="logo">QUIZZITY</a>
</header>

<main style="max-width:700px; margin:30px auto; padding:0 16px;">
    <h1><?= htmlspecialchars($quiz['title']) ?></h1>
    <p><?= htmlspecialchars($quiz['description'] ?? '') ?></p>

    <?php if (!$questions): ?>

        <p>This quiz has no questions yet.</p>
        <a href="quizes.php">← Back to quizzes</a>

    <?php elseif ($submitted): ?>

        <h2>Your result: <?= $score ?> / <?= count($questions) ?></h2>

        <?php foreach ($questions as $n => $q): ?>
            <fieldset style="margin-bottom:16px">
                <legend><?= $n + 1 ?>. <?= htmlspecialchars($q['question_text']) ?></legend>

                <?php foreach ($answersQuestions[$q['id']] as $a): ?>
                    <?php
                        $isChosen  = (int)$a['id'] === ($chosen[(int)$q['id']] ?? 0);
                        $isCorrect = (bool)$a['is_correct'];
                        $style = '';
                        $mark  = '';
                        if ($isCorrect)    { $style = 'color:green;font-weight:bold'; $mark = ' ✔'; }
                        elseif ($isChosen) { $style = 'color:red'; $mark = ' ✘'; }
                    ?>
                    <div style="<?= $style ?>">
                        <?= htmlspecialchars($a['answer_text']) ?><?= $mark ?>
                        <?= $isChosen ? '(your answer)' : '' ?>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>

        <p>
            <a href="quiz.php?id=<?= $id ?>">Play again</a> |
            <a href="quizes.php">Back to quizzes</a>
        </p>

    <?php else: ?>

        <form method="post">
            <?php foreach ($questions as $n => $q): ?>
                <fieldset style="margin-bottom:16px">
                    <legend><?= $n + 1 ?>. <?= htmlspecialchars($q['question_text']) ?></legend>

                    <?php foreach ($answersQuestions[$q['id']] as $a): ?>
                        <label style="display:block; margin:6px 0">
                            <input type="radio" name="q[<?= (int)$q['id'] ?>]" value="<?= (int)$a['id'] ?>" required>
                            <?= htmlspecialchars($a['answer_text']) ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>

            <button type="submit">Finish</button>
        </form>

    <?php endif; ?>
</main>

</body>
</html>