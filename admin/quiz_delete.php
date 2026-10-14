<?php
require_once __DIR__ . '/admin_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = 'Invalid quiz id';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, title FROM quizzes WHERE id = ?');
$stmt->execute([$id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    $_SESSION['flash'] = 'Quiz not found';
    header('Location: index.php');
    exit;
}

$pdo->beginTransaction();
try {
    $pdo->prepare('DELETE FROM answers WHERE question_id IN (SELECT id FROM questions WHERE quiz_id = ?)')
        ->execute([$id]);
    $pdo->prepare('DELETE FROM questions WHERE quiz_id = ?')->execute([$id]);   
    $pdo->prepare('DELETE FROM quizzes WHERE id = ?')->execute([$id]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = 'Could not delete the quiz: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}

$_SESSION['flash'] = 'Quiz "' . $quiz['title'] . '" deleted';
header('Location: ../index.php');
exit;