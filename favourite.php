<?php
session_start();
require_once 'database.php';
header('Content-Type: application/json');

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'login required']);
        exit;
    }

    $userId = (int)$_SESSION['user_id'];
    $quizId = (int)($_POST['quiz_id'] ?? 0);

    if ($quizId <= 0) {
        throw new Exception('quiz_id was not received');
    }

    $del = $pdo->prepare('DELETE FROM favourites WHERE user_id = ? AND quiz_id = ?');
    $del->execute([$userId, $quizId]);

    if ($del->rowCount() > 0) {
        $isFavourite = false;
    } else {
        $pdo->prepare('INSERT INTO favourites (user_id, quiz_id) VALUES (?, ?)')
            ->execute([$userId, $quizId]);
        $isFavourite = true;
    }

    echo json_encode(['favourite' => $isFavourite]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}