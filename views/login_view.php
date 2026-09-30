<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Quizzity</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <main class="auth">
        <form class="auth-card" method="POST" action="login.php">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <h1>Welcome back</h1>
            <p class="auth-sub">Log in to keep playing on QUIZZITY.</p>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       placeholder="Enter email..." autocomplete="email" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password"
                       placeholder="Enter password..." autocomplete="current-password" required>
            </div>

            <button type="submit" class="auth-button">Login</button>

            <p class="auth-switch">
                New here? <a href="register.php">Create an account</a>
            </p>
        </form>
    </main>

</body>
</html>