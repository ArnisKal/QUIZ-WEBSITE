<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Quizzity</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <div class="password_wrap">
                    <input id="password" name="password" type="password"
                           placeholder="Enter password..." autocomplete="new-password" required>

                    <button type="button" class="password_toggle" aria-label="Show password">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                            <line class="eye_slash" x1="3" y1="3" x2="21" y2="21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="field">
                <label for="password_repeat">Repeat password</label>
                <div class="password_wrap">
                    <input id="password_repeat" name="password_repeat" type="password"
                           placeholder="Repeat password..." autocomplete="new-password" required>

                    <button type="button" class="password_toggle" aria-label="Show password">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                            <line class="eye_slash" x1="3" y1="3" x2="21" y2="21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="auth-button">Register</button>

            <p class="auth-switch">
                Already have an account? <a href="login.php">Login</a>
            </p>

        </form>
    </main>

<script>

document.querySelectorAll('.password_wrap').forEach(function (wrap) {
    var input = wrap.querySelector('input');
    var btn = wrap.querySelector('.password_toggle');

    btn.addEventListener('click', function () {
        var show = input.type === 'password';

        input.type = show ? 'text' : 'password';
        btn.classList.toggle('is_visible', show);
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
});
</script>

</body>
</html>