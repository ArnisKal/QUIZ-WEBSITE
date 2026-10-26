<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'database.php';
require_once 'theme.php';

// Create CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// If already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Login form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check CSRF
    if (
        empty($_POST['csrf']) ||
        !hash_equals($_SESSION['csrf'], $_POST['csrf'])
    ) {
        die('Invalid CSRF token.');
    }

    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Find user
    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash, role
         FROM users
         WHERE email = ?'
    );

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check password
    if ($user && password_verify($password, $user['password_hash'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header('Location: index.php');
        exit;
    }

    // Wrong email/password
    $error = 'Invalid email or password.';
}

require 'views/login_view.php';
