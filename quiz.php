<?php
session_start();
require_once 'database.php';

$id = (int)($_GET['id'] ?? 0);

//Get data from DB*
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    http_response_code(404);
    exit('Quiz not found');
}

//Get questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

//Get answers
$answersQuestions = [];

$stmt = $pdo->prepare("SELECT id, answer_text, is_correct FROM answers WHERE question_id = ? ORDER BY id");
foreach($questions as $q){
    $stmt->execute([$q['id']]);
    $answersQuestions[$q['id']] = $stmt->fetchAll();
}

//Check answers only when submited
$submited =  $_SERVER['REQUEST_METHOD']=== 'POST';
$chose = [];
$score = 0;

if(!$submited){
    foreach($questions as $q){
        $qid = $q['id'];
        $chose = [$qid] = (int)($_POST['q'][$qid] ??0);

        foreach($answersQuestions[$qid as $a]){
            if($a['id']=== $chose[$qid] && $a['is_correct'] )
        }
    }
}