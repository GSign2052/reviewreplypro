<?php
require dirname(__DIR__, 2) . '/bootstrap/app.php';

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Database.php';
require ROOT . '/app/Admin/AdminRepository.php';
require ROOT . '/app/Middleware/requireAdmin.php';

$repo        = new AdminRepository(Database::connect());
$stats       = $repo->getStats();
$readiness   = $repo->getLiveReadiness();
$csrfToken   = SessionManager::csrfToken();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — ReviewReplyPro</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

<?php include ROOT . '/public/admin/_nav.php'; ?>

<main class="admin-main">

    <div class="admin-internal-badge">Internal only — nicht für Kunden sichtbar</div>

    <h1 class="admin-page-title">Übersicht</h1>
    <p class="admin-env-badge env-<?= htmlspecialchars(appEnv(), ENT_QUOTES) ?>">
        Umgebung: <?= htmlspecialchars(strtoupper(appEnv()), ENT_QUOTES) ?>
    </p>

    <!-- Statistik-Kacheln -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_orgs'] ?></div>
            <div class="stat-label">Organisationen</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_users'] ?></div>
            <div class="stat-label">Nutzer gesamt</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['new_today'] ?></div>
            <div class="stat-label">Neue Nutzer heute</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['new_week'] ?></div>
            <div class="stat-label">Neue Nutzer (7 Tage)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_replies'] ?></div>
            <div class="stat-label">Generierte Antworten</div>
        </div>
    </div>

    <!-- Live-Checkliste -->
    <section class="admin-section">
        <h2>Live-Checkliste</h2>
        <table class="admin-table">
            <tbody>
                <tr>
                    <td><?= $readiness['env_is_production'] ? '✅' : '❌' ?></td>
                    <td>APP_ENV = production</td>
                    <td><?= appEnv() ?></td>
                </tr>
                <tr>
                    <td><?= $readiness['https_env'] ? '✅' : '⚠️' ?></td>
                    <td>HTTPS / APP_URL mit https://</td>
                    <td><?= htmlspecialchars(env('APP_URL', '–'), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td><?= $readiness['stripe_keys_set'] ? '✅' : '❌' ?></td>
                    <td>Stripe Live-Keys gesetzt</td>
                    <td><?= $readiness['stripe_keys_set'] ? 'Live' : 'Test/ungesetzt' ?></td>
                </tr>
                <tr>
                    <td><?= $readiness['billing_enabled'] ? '✅' : '❌' ?></td>
                    <td>BILLING_ENABLED = true</td>
                    <td><?= $readiness['billing_enabled'] ? 'Ja' : 'Nein' ?></td>
                </tr>
                <tr>
                    <td><?= $readiness['legal_live'] ? '✅' : '❌' ?></td>
                    <td>LEGAL_MODE = true</td>
                    <td><?= $readiness['legal_live'] ? 'Live-Texte' : 'Platzhalter' ?></td>
                </tr>
            </tbody>
        </table>
    </section>

</main>
</body>
</html>
