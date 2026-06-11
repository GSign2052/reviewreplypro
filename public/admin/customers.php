<?php
require dirname(__DIR__, 2) . '/bootstrap/app.php';

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Database.php';
require ROOT . '/app/Admin/AdminRepository.php';
require ROOT . '/app/Middleware/requireAdmin.php';

$repo      = new AdminRepository(Database::connect());
$customers = $repo->getCustomers();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kunden — Admin</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<?php include ROOT . '/public/admin/_nav.php'; ?>
<main class="admin-main">
    <div class="admin-internal-badge">Internal only — nicht für Kunden sichtbar</div>
    <h1 class="admin-page-title">Kunden <span class="admin-count"><?= count($customers) ?></span></h1>

    <table class="admin-table admin-table-full">
        <thead>
            <tr>
                <th>Organisation</th>
                <th>E-Mail</th>
                <th>Registriert</th>
                <th>Antworten</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($customers as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['org_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= date('d.m.Y', strtotime($c['user_created'])) ?></td>
                <td><?= (int) $c['reply_count'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($customers)): ?>
            <tr><td colspan="4" style="color:var(--muted);text-align:center">Keine Kunden gefunden.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>
