<?php
require dirname(__DIR__, 2) . '/bootstrap/app.php';

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Database.php';
require ROOT . '/app/Admin/AdminRepository.php';
require ROOT . '/app/Middleware/requireAdmin.php';

$repo    = new AdminRepository(Database::connect());
$billing = $repo->getBillingStatus();
$enabled = billingEnabled();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing — Admin</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<?php include ROOT . '/public/admin/_nav.php'; ?>
<main class="admin-main">
    <div class="admin-internal-badge">Internal only — nicht für Kunden sichtbar</div>
    <h1 class="admin-page-title">Billing-Status</h1>

    <?php if (!$enabled): ?>
        <div class="admin-notice">
            BILLING_ENABLED ist <strong>false</strong> — Stripe ist deaktiviert. Abo-Daten nur nach Aktivierung sichtbar.
        </div>
    <?php endif; ?>

    <?php if (empty($billing)): ?>
        <p style="color:var(--muted)">Noch keine Abo-Datensätze vorhanden.</p>
    <?php else: ?>
        <table class="admin-table admin-table-full">
            <thead>
                <tr>
                    <th>Organisation</th>
                    <th>Tarif</th>
                    <th>Status</th>
                    <th>Nächste Abrechnung</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($billing as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['org_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($b['plan_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="sub-status sub-status-<?= htmlspecialchars($b['status'], ENT_QUOTES) ?>"><?= htmlspecialchars($b['status'], ENT_QUOTES) ?></span></td>
                    <td><?= $b['current_period_end'] ? date('d.m.Y', strtotime($b['current_period_end'])) : '–' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
