<?php
session_start();
require 'database.php';  

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

$theme = $_POST['theme'] ?? '';

if (!in_array($theme, ['light', 'dark'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$stmt = $pdo->prepare('UPDATE users SET theme = ? WHERE id = ?');
$stmt->execute([$theme, $_SESSION['user_id']]);
$_SESSION['theme'] = $theme;

echo json_encode(['ok' => true]);   