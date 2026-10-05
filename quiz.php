<?php
session_start();
require_once 'database.php';

$id = (int)($_GET['id'] ?? 0);

// Get quiz
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    exit('Quiz not found');
}

// Get questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_number, id");
$stmt->execute([$id]);
$questions = $stmt->fetchAll();

// Get answers for each question
$answersQuestions = [];

$stmt = $pdo->prepare("SELECT id, answer_text, is_correct FROM answers WHERE question_id = ? ORDER BY id");
foreach ($questions as $q) {
    $stmt->execute([$q['id']]);
    $answersQuestions[$q['id']] = $stmt->fetchAll();
}

// Check answers only when submitted
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$chosen = [];
$score = 0;

if ($submitted) {
    foreach ($questions as $q) {
        $qid = $q['id'];
        $chosen[$qid] = (int)($_POST['q'][$qid] ?? 0);

        foreach ($answersQuestions[$qid] as $a) {
            if ((int)$a['id'] === $chosen[$qid] && $a['is_correct']) {
                $score++;
            }
        }
    }
}
?>