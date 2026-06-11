<?php
require dirname(__DIR__) . '/bootstrap/app.php';

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Database.php';
require ROOT . '/app/Validator.php';
require ROOT . '/app/Auth/SessionManager.php';
require ROOT . '/app/Auth/UserRepository.php';
require ROOT . '/app/Auth/AuthService.php';

SessionManager::start();

// Bereits eingeloggt → direkt zur App
if (SessionManager::isLoggedIn()) {
    header('Location: /');
    exit;
}

$errors  = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SessionManager::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ungültige Anfrage. Bitte Seite neu laden.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $oldEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

        $validator = new Validator();
        if (!$validator->validateLoginInput(['email' => $email, 'password' => $password])) {
            $errors = $validator->getErrors();
        } else {
            $auth = new AuthService(Database::connect());
            if ($auth->login($email, $password)) {
                header('Location: /');
                exit;
            } else {
                $errors[] = 'E-Mail oder Passwort ist falsch.';
            }
        }
    }
}

$csrf = SessionManager::csrfToken();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmelden – ReviewReplyPro</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 1.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .auth-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--text);
        }
        .auth-field {
            margin-bottom: 1rem;
        }
        .auth-field label {
            display: block;
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .auth-field input {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.15s;
        }
        .auth-field input:focus {
            outline: none;
            border-color: var(--accent);
        }
        .auth-btn {
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: opacity 0.15s;
        }
        .auth-btn:hover { opacity: 0.88; }
        .auth-errors {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #f87171;
        }
        .auth-foot {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: var(--muted);
        }
        .auth-foot a {
            color: var(--accent);
            text-decoration: none;
        }
        .auth-foot a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            ReviewReply<span class="logo-accent">Pro</span>
        </div>
        <div class="auth-title">Anmelden</div>

        <?php if ($errors): ?>
        <div class="auth-errors">
            <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach ?>
        </div>
        <?php endif ?>

        <form method="POST" action="/login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <div class="auth-field">
                <label for="email">E-Mail-Adresse</label>
                <input type="email" id="email" name="email"
                       value="<?= $oldEmail ?>"
                       autocomplete="email" required autofocus>
            </div>

            <div class="auth-field">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>

            <button type="submit" class="auth-btn">Anmelden</button>
        </form>

        <div class="auth-foot">
            Noch kein Konto?
            <a href="/register.php">Jetzt registrieren</a>
        </div>
    </div>
</div>
</body>
</html>
