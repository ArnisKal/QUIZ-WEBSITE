<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Quizzity</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <main class="auth">
        <form class="auth-card" method="POST" action="register.php">

            <h1>Create your account</h1>
            <p class="auth-sub">Join QUIZZITY and start being yourself.</p>

            <?php if (!empty($errors)): ?>
                <div class="auth-errors" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- CSRF -->
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text"
                       placeholder="Enter username..." autocomplete="username"
                       value="<?= htmlspecialchars($username) ?>" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       placeholder="Enter email..." autocomplete="email"
                       value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password"
                       placeholder="Enter password..." autocomplete="new-password" required>
            </div>

            <div class="field">
                <label for="password_repeat">Repeat password</label>
                <input id="password_repeat" name="password_repeat" type="password"
                       placeholder="Repeat password..." autocomplete="new-password" required>
            </div>

            <button type="submit" class="auth-button">Register</button>

            <p class="auth-switch">
                Already have an account? <a href="login.php">Login</a>
            </p>

        </form>
    </main>

</body>
</html>