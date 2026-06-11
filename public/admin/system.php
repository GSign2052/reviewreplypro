<?php
require dirname(__DIR__, 2) . '/bootstrap/app.php';

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Database.php';
require ROOT . '/app/Admin/AdminRepository.php';
require ROOT . '/app/Middleware/requireAdmin.php';

$repo   = new AdminRepository(Database::connect());
$errors = $repo->getRecentErrors(50);
$phpVer = PHP_VERSION;
$dbVer  = Database::connect()->query('SELECT VERSION()')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System — Admin</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<?php include ROOT . '/public/admin/_nav.php'; ?>
<main class="admin-main">
    <div class="admin-internal-badge">Internal only — nicht für Kunden sichtbar</div>
    <h1 class="admin-page-title">System</h1>

    <section class="admin-section">
        <h2>Versionen</h2>
        <table class="admin-table">
            <tbody>
                <tr><td>PHP</td><td><?= htmlspecialchars($phpVer, ENT_QUOTES) ?></td></tr>
                <tr><td>MySQL</td><td><?= htmlspecialchars($dbVer, ENT_QUOTES) ?></td></tr>
                <tr><td>APP_ENV</td><td><?= htmlspecialchars(appEnv(), ENT_QUOTES) ?></td></tr>
                <tr><td>APP_URL</td><td><?= htmlspecialchars(env('APP_URL', '–'), ENT_QUOTES) ?></td></tr>
                <tr><td>Billing</td><td><?= billingEnabled() ? '✅ aktiv' : '❌ inaktiv' ?></td></tr>
                <tr><td>Legal</td><td><?= legalLive() ? '✅ live' : '❌ Platzhalter' ?></td></tr>
            </tbody>
        </table>
    </section>

    <section class="admin-section">
        <h2>Letzte Log-Einträge
            <span class="admin-count"><?= count($errors) ?></span>
            <small style="font-size:0.75rem;color:var(--muted);font-weight:400;margin-left:0.5rem">
                storage/logs/app.log
            </small>
        </h2>
        <?php if (empty($errors)): ?>
            <p style="color:var(--muted)">Keine Log-Einträge.</p>
        <?php else: ?>
            <div class="log-viewer">
                <?php foreach ($errors as $line): ?>
                    <div class="log-line"><?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
