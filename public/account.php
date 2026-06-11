<?php
require dirname(__DIR__) . '/bootstrap/app.php';

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Database.php';
require ROOT . '/app/Auth/SessionManager.php';
require ROOT . '/app/Middleware/requireAuth.php';

$csrfToken = SessionManager::csrfToken();
$orgId     = (int) $_SESSION['org_id'];

// Anzahl gespeicherter Antworten
$pdo   = Database::connect();
$stmt  = $pdo->prepare('SELECT COUNT(*) FROM review_replies WHERE org_id = ?');
$stmt->execute([$orgId]);
$replyCount = (int) $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konto — ReviewReplyPro</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/billing.css">
    <link rel="stylesheet" href="/assets/css/legal.css">
</head>
<body>
<header class="app-header">
    <div class="header-inner">
        <a href="/" class="logo-link"><span class="logo">ReviewReply<span class="logo-accent">Pro</span></span></a>
        <nav class="header-nav">
            <a href="/" class="nav-link">App</a>
            <a href="/billing.php" class="nav-link">Abo & Rechnung</a>
            <a href="/account.php" class="nav-link nav-active">Konto</a>
        </nav>
        <div class="user-nav" style="margin-left:auto">
            <span><?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?></span>
            <form method="POST" action="/logout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn-logout">Abmelden</button>
            </form>
        </div>
    </div>
</header>

<main class="billing-main">
    <h1 style="font-size:1.5rem;margin-bottom:1.5rem">Konto</h1>

    <!-- Kontodaten -->
    <section class="account-section">
        <h2>Kontodaten</h2>
        <table class="admin-table" style="max-width:480px">
            <tr><td style="color:var(--muted)">E-Mail</td><td><?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><td style="color:var(--muted)">Organisation</td><td><?= htmlspecialchars($_SESSION['org_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><td style="color:var(--muted)">Gespeicherte Antworten</td><td><?= $replyCount ?></td></tr>
        </table>
    </section>

    <!-- Datenschutz & DSGVO -->
    <section class="account-section">
        <h2>Datenschutz & Ihre Daten</h2>
        <div class="account-dsgvo">
            <p><strong>Was wir speichern:</strong> E-Mail, Organisationsname, Session-Daten und die von Ihnen erzeugten Antwortverläufe.</p>
            <p><strong>Was wir nie tun:</strong> Antworten automatisch veröffentlichen, Ihre Daten verkaufen oder für Werbung nutzen.</p>
            <p><strong>Ihre Rechte nach DSGVO:</strong> Auskunft, Berichtigung, Löschung, Datenübertragbarkeit.</p>
            <p class="account-legal-note">
                Hinweis: Antworten immer manuell prüfen vor dem Kopieren — keine automatische Veröffentlichung.
            </p>
        </div>
        <div class="account-actions">
            <div class="account-action-item">
                <div>
                    <div class="account-action-title">Eigene Daten exportieren</div>
                    <div class="account-action-desc">JSON-Export aller gespeicherten Antworten</div>
                </div>
                <button class="btn-ghost" disabled title="Demnächst verfügbar">Demnächst</button>
            </div>
            <div class="account-action-item account-action-danger">
                <div>
                    <div class="account-action-title">Konto löschen</div>
                    <div class="account-action-desc">Löscht das Konto und alle gespeicherten Daten unwiderruflich</div>
                </div>
                <button class="btn-danger" disabled title="Demnächst verfügbar">Demnächst</button>
            </div>
        </div>
    </section>

    <!-- Support -->
    <section class="account-section">
        <h2>Support & Kontakt</h2>
        <p style="color:var(--muted);font-size:0.875rem;margin-bottom:0.75rem">
            Fragen, Probleme oder Feedback:
        </p>
        <a href="mailto:felixschmidt0601@web.de" class="btn-primary" style="display:inline-block;text-decoration:none;width:auto;padding:0.6rem 1.25rem">
            E-Mail schreiben
        </a>
    </section>

</main>
<?php include ROOT . '/public/_footer.php'; ?>
</body>
</html>
