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

            <?php if (!empty($error)): ?>
                <p class="form_error" role="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       placeholder="Enter email..." autocomplete="email" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="password_wrap">
                    <input id="password" name="password" type="password"
                           placeholder="Enter password..." autocomplete="current-password" required>

                    <button type="button" class="password_toggle" id="togglePassword" aria-label="Show password">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                            <line class="eye_slash" x1="3" y1="3" x2="21" y2="21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="auth-button">Login</button>

            <p class="auth-switch">
                New here? <a href="register.php">Create an account</a>
            </p>
        </form>
    </main>

<script>
var passwordInput = document.getElementById('password');
var toggleBtn = document.getElementById('togglePassword');

toggleBtn.addEventListener('click', function () {
    var show = passwordInput.type === 'password';

    passwordInput.type = show ? 'text' : 'password';
    toggleBtn.classList.toggle('is_visible', show);
    toggleBtn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
});
</script>

</body>
</html>