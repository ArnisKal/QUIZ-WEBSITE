<?php

session_start();

require_once 'database.php';

$quiz_id = $_GET['id'] ?? null;

if (!$quiz_id) {
    exit('Quiz not found.');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM questions
    WHERE quiz_id = ?
    ORDER BY question_number
");

$stmt->execute([$quiz_id]);

$questions = $stmt->fetchAll();

if (!$questions) {
    exit('No questions found for this quiz.');
}

$current = isset($_GET['q']) ? (int)$_GET['q'] : 0;

if ($current < 0 || $current >= count($questions)) {
    $current = 0;
}

$question = $questions[$current];

