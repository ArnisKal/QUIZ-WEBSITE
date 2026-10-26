<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'database.php';

$theme = 'light';

if (!empty($_SESSION['theme'])) {
    $theme = $_SESSION['theme'];
} elseif (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT theme FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $theme = $stmt->fetchColumn() ?: 'light';
    $_SESSION['theme'] = $theme;
}