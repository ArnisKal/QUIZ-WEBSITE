<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'database.php';
require_once 'theme.php';

$errors = [];
$username = '';
$email = '';

// Create CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// If user is logged in, send him to the main page
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}



// Run when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $repeat = $_POST['password_repeat'] ?? '';

    // Validate
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = 'Username must be 3–30 characters: letters, numbers or underscores.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $repeat) {
        $errors[] = 'Passwords do not match.';
    }
    

    // Check for duplicates
    if (!$errors) {
        $stmt = $pdo->prepare(
            'SELECT username, email FROM users WHERE username = ? OR email = ?'
        );
        $stmt->execute([$username, $email]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (strcasecmp($row['username'], $username) === 0) {
                $errors[] = 'That username is already taken.';
            }

            if (strcasecmp($row['email'], $email) === 0) {
                $errors[] = 'An account with that email already exists.';
            }
        }
    }

    // Save the user
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash)
            VALUES (?, ?, ?)'
        );

        $stmt->execute([$username, $email, $hash]);

        header('Location: login.php?registered=1');
        exit;
    }
}

require 'views/register_view.php';
