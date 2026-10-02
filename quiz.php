<?php
session_start();
require_once 'database.php';

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current = isset($_GET['q']) ? (int)$_GET['q'] : 0;

if (!$quiz_id) {
    exit('Quiz not found.');
}

var_dump("Hello");


/*Get answers*/
$stmt = $pdo->prepare(
    'SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_number'
);

$stmt->execute([$quiz_id]);

$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    exit('No questions found for this quiz.');
}

/* Check is number valid  */
if ($current < 0 || $current >= count($questions)) {
    $current = 0;
}

$question = $questions[$current];


/*Get answers*/
$stmt = $pdo->prepare(
    'SELECT id, text, is_correct FROM answers WHERE question_id = ? ORDER BY id'
);

$stmt->execute([$question['id']]);

$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$answers) {
    exit('No answers found for this question.');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="game">
        <div class="question_container"></div>

        <p>Question <?= $current + 1?> of <?= count($questions)?> </p>

        <h2> <?= htmlspecialchars($question['question_text']) ?> </h2>
    </div>
</body>
</html>

