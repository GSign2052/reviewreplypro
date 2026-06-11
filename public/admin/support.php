<?php
require dirname(__DIR__, 2) . '/bootstrap/app.php';

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Database.php';
require ROOT . '/app/Middleware/requireAdmin.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support — Admin</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<?php include ROOT . '/public/admin/_nav.php'; ?>
<main class="admin-main">
    <div class="admin-internal-badge">Internal only — nicht für Kunden sichtbar</div>
    <h1 class="admin-page-title">Support</h1>

    <div class="admin-notice">
        <strong>Support-System:</strong> Noch kein Ticket-System eingebunden.
        Eingehende Anfragen aktuell per E-Mail an
        <a href="mailto:felixschmidt0601@web.de" style="color:var(--accent)">felixschmidt0601@web.de</a>.
    </div>

    <section class="admin-section">
        <h2>Kontaktformular-Einstellungen</h2>
        <table class="admin-table">
            <tbody>
                <tr>
                    <td>Kontakt-E-Mail</td>
                    <td><?= htmlspecialchars(env('SUPPORT_EMAIL', 'felixschmidt0601@web.de'), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Support-Kanal</td>
                    <td><span style="color:var(--medium)">⚠️ Nur E-Mail — kein Ticket-System</span></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="admin-section">
        <h2>Roadmap: Support-Kanal</h2>
        <ul style="color:var(--muted);font-size:0.875rem;line-height:2;padding-left:1.5rem">
            <li>Kontaktformular mit Mailto-Fallback <span style="color:var(--low)">(implementiert in Phase 5)</span></li>
            <li>Support-Ticket-System (Linear / Notion / eigenes)</li>
            <li>In-App-Feedback-Button</li>
            <li>Status-Page-Link (z.B. Statuspage.io)</li>
        </ul>
    </section>
</main>
</body>
</html>
