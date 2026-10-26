<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'site_check.php';
require_once 'database.php';
require_once 'countries.php';

$userId = (int)$_SESSION['user_id'];

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$tabs = [
    'profile'  => 'Profile',
    'email'    => 'Email',
    'password' => 'Password',
    'delete'   => 'Delete account',
];
$tab = $_GET['tab'] ?? 'profile';
if (!isset($tabs[$tab])) {
    $tab = 'profile';
}

$stmt = $pdo->prepare('SELECT id, username, email, password_hash, role, country FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

function go(string $tab): void {
    header('Location: account.php?tab=' . $tab);
    exit;
}

$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$error = '';

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$action = $isPost ? ($_POST['action'] ?? '') : '';

if ($isPost) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $error = 'Session expired. Please try again.';

    } elseif ($action === 'update_username') {
        $new = trim($_POST['username'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $new)) {
            $error = 'Username: 3-30 characters, only letters, numbers and _';
        } else {
            $c = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id <> ?');
            $c->execute([$new, $userId]);
            if ($c->fetch()) {
                $error = 'This username is already taken';
            } else {
                $pdo->prepare('UPDATE users SET username = ? WHERE id = ?')->execute([$new, $userId]);
                $_SESSION['username'] = $new;
                $_SESSION['flash'] = 'Username updated';
                go('profile');
            }
        }

    } elseif ($action === 'update_email') {
        $new = trim($_POST['email'] ?? '');
        if (!password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            $error = 'Wrong password';
        } elseif (!filter_var($new, FILTER_VALIDATE_EMAIL) || mb_strlen($new) > 255) {
            $error = 'Please enter a valid email';
        } else {
            $c = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
            $c->execute([$new, $userId]);
            if ($c->fetch()) {
                $error = 'This email is already used';
            } else {
                $pdo->prepare('UPDATE users SET email = ? WHERE id = ?')->execute([$new, $userId]);
                $_SESSION['flash'] = 'Email updated';
                go('email');
            }
        }

    } elseif ($action === 'update_password') {
        $new = $_POST['new_password'] ?? '';
        if (!password_verify($_POST['current_password'] ?? '', $user['password_hash'])) {
            $error = 'Current password is wrong';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters';
        } elseif ($new !== ($_POST['confirm_password'] ?? '')) {
            $error = 'Passwords do not match';
        } else {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
            session_regenerate_id(true);
            $_SESSION['flash'] = 'Password changed';
            go('password');
        }

    } elseif ($action === 'update_country') {
        $code = strtoupper(trim($_POST['country'] ?? ''));
        if ($code !== '' && !isset(countries()[$code])) {
            $error = 'Unknown country';
        } else {
            $pdo->prepare('UPDATE users SET country = ? WHERE id = ?')
                ->execute([$code !== '' ? $code : null, $userId]);
            $_SESSION['flash'] = 'Country updated';
            go('profile');
        }

    } elseif ($action === 'delete_account') {
        if ($user['role'] === 'admin') {
            $error = 'Admin accounts cannot be deleted here';
        } elseif (!password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            $error = 'Wrong password';
        } else {
            try {
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
                $_SESSION = [];
                session_destroy();
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'This account owns quizzes and cannot be deleted yet. Ask an admin for help.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - Quizzity</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">QUIZZITY</a>
    <nav class="nav">
        <a href="quizes.php">Quizzes</a>
        <a href="leaderboards.php">Leaderboards</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="acc_page">

    <nav class="acc_nav" aria-label="Account sections">
        <?php foreach ($tabs as $key => $label):
            $classes = array_filter([
                $key === $tab ? 'active' : '',
                $key === 'delete' ? 'danger' : '',
            ]);
        ?>
            <a href="account.php?tab=<?= $key ?>"<?= $classes ? ' class="' . implode(' ', $classes) . '"' : '' ?><?= $key === $tab ? ' aria-current="page"' : '' ?>><?= $label ?></a>
        <?php endforeach; ?>
    </nav>

    <section class="acc_panel">
        <h1><?= $tabs[$tab] ?></h1>

        <?php if ($message): ?>
            <p class="flash_ok" role="status"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="form_error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($tab === 'profile'): ?>

            <div class="pi">

                <details class="pi_row" <?= $error && $action === 'update_username' ? 'open' : '' ?>>
                    <summary>
                        <div>
                            <span class="pi_label">Nickname</span>
                            <span class="pi_value"><?= htmlspecialchars($user['username']) ?></span>
                        </div>
                        <span class="pi_edit">Edit</span>
                    </summary>
                    <form method="post" action="account.php?tab=profile" class="form_stack pi_form">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <input type="hidden" name="action" value="update_username">
                        <div class="field">
                            <label for="username">New nickname</label>
                            <input id="username" name="username" maxlength="30" required autocomplete="nickname"
                                   value="<?= htmlspecialchars($action === 'update_username' ? ($_POST['username'] ?? '') : $user['username']) ?>">
                            <span class="field_hint">3-30 characters: letters, numbers and _</span>
                        </div>
                        <div class="form_actions">
                            <button type="submit" class="btn btn_dark">Save</button>
                        </div>
                    </form>
                </details>

                <details class="pi_row" <?= $error && $action === 'update_country' ? 'open' : '' ?>>
                    <summary>
                        <div>
                            <span class="pi_label">Country</span>
                            <span class="pi_value">
                                <?php if ($user['country'] && isset(countries()[$user['country']])): ?>
                                    <?= htmlspecialchars(countries()[$user['country']]) ?> <?= flag_html($user['country']) ?>
                                <?php else: ?>
                                    Not selected
                                <?php endif; ?>
                            </span>
                        </div>
                        <span class="pi_edit">Edit</span>
                    </summary>
                    <form method="post" action="account.php?tab=profile" class="form_stack pi_form">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <input type="hidden" name="action" value="update_country">
                        <div class="field">
                            <label for="country">Select your country</label>
                            <select id="country" name="country">
                                <option value="">Not selected</option>
                                <?php foreach (countries() as $code => $name): ?>
                                    <option value="<?= $code ?>" <?= $user['country'] === $code ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form_actions">
                            <button type="submit" class="btn btn_dark">Save</button>
                        </div>
                    </form>
                </details>

            </div>

        <?php elseif ($tab === 'email'): ?>

            <form method="post" action="account.php?tab=email" class="form_stack">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                <input type="hidden" name="action" value="update_email">
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" maxlength="255" required autocomplete="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
                </div>
                <div class="field">
                    <label for="password">Password <span class="field_hint">(to confirm the change)</span></label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                </div>
                <div class="form_actions">
                    <button type="submit" class="btn btn_dark">Save</button>
                </div>
            </form>

        <?php elseif ($tab === 'password'): ?>

            <form method="post" action="account.php?tab=password" class="form_stack">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                <input type="hidden" name="action" value="update_password">
                <div class="field">
                    <label for="current_password">Current password</label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                </div>
                <div class="field">
                    <label for="new_password">New password</label>
                    <input id="new_password" type="password" name="new_password" minlength="8" required autocomplete="new-password">
                    <span class="field_hint">At least 8 characters</span>
                </div>
                <div class="field">
                    <label for="confirm_password">Repeat new password</label>
                    <input id="confirm_password" type="password" name="confirm_password" minlength="8" required autocomplete="new-password">
                </div>
                <div class="form_actions">
                    <button type="submit" class="btn btn_dark">Change password</button>
                </div>
            </form>

        <?php else: ?>

            <p class="acc_warn">Deleting your account is permanent. Your favourites will be removed too.</p>
            <form method="post" action="account.php?tab=delete" class="form_stack"
                  onsubmit="return confirm('Delete your account forever?');">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                <input type="hidden" name="action" value="delete_account">
                <div class="field">
                    <label for="del_password">Enter your password to confirm</label>
                    <input id="del_password" type="password" name="password" required autocomplete="current-password">
                </div>
                <div class="form_actions">
                    <button type="submit" class="btn btn_danger">Delete my account</button>
                </div>
            </form>

        <?php endif; ?>
    </section>

</main>

</body>
</html>