<?php 
require_once 'admin/admin_check.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo-> prepare('SELECT id FROM quizzes WHERE id = ?');
$stmt->execute(['id']);
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
