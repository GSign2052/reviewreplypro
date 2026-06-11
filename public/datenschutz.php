<?php
require dirname(__DIR__) . '/bootstrap/app.php';
require ROOT . '/config/security-headers.php';
$legalLive = legalLive();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenschutz — ReviewReplyPro</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/legal.css">
</head>
<body>
<header class="app-header">
    <div class="header-inner">
        <a href="/" class="logo-link"><span class="logo">ReviewReply<span class="logo-accent">Pro</span></span></a>
    </div>
</header>
<main class="legal-main">

    <?php if (!$legalLive): ?>
        <div class="legal-placeholder-banner">
            Platzhalter — Vor Livegang durch geprüfte Datenschutzerklärung ersetzen (DSGVO-Anwalt empfohlen)
        </div>
    <?php endif; ?>

    <h1>Datenschutzerklärung</h1>
    <p class="legal-last-updated">Stand: <?= date('d.m.Y') ?></p>

    <div class="legal-placeholder-content">
        <h2>1. Verantwortlicher</h2>
        <p>[Name und Kontakt des Verantwortlichen laut Impressum]</p>

        <h2>2. Welche Daten wir speichern</h2>
        <ul>
            <li>E-Mail-Adresse und Organisationsname bei Registrierung</li>
            <li>Sitzungsdaten (Session-Cookie, temporär)</li>
            <li>Von Ihnen eingegebene Bewertungstexte zur Generierung von Antworten</li>
            <li>Generierte Antworten im Verlauf (bis zur manuellen Löschung)</li>
            <?php if (billingEnabled()): ?>
            <li>Abrechnungsdaten über Stripe (Name, E-Mail, Zahlungsmethode)</li>
            <?php endif; ?>
        </ul>

        <h2>3. Zweck der Verarbeitung</h2>
        <ul>
            <li>Bereitstellung des ReviewReplyPro-Dienstes</li>
            <li>Authentifizierung und Kontoverwaltung</li>
            <?php if (billingEnabled()): ?>
            <li>Abwicklung von Zahlungen über Stripe</li>
            <?php endif; ?>
        </ul>

        <h2>4. Weitergabe an Dritte</h2>
        <p>Bewertungstexte werden zur KI-Antwortgenerierung an einen KI-Dienst übermittelt.
            [Anbieter, Datenschutzlink des Anbieters einfügen]</p>
        <?php if (billingEnabled()): ?>
        <p>Zahlungsdaten werden an Stripe Inc. übermittelt.
            <a href="https://stripe.com/de/privacy" target="_blank" rel="noopener">Stripe-Datenschutz</a></p>
        <?php endif; ?>

        <h2>5. Ihre Rechte (DSGVO Art. 15–22)</h2>
        <ul>
            <li>Auskunft über gespeicherte Daten</li>
            <li>Berichtigung unrichtiger Daten</li>
            <li>Löschung Ihrer Daten (Konto löschen — demnächst verfügbar)</li>
            <li>Datenübertragbarkeit (Export — demnächst verfügbar)</li>
            <li>Widerspruch gegen die Verarbeitung</li>
        </ul>
        <p>Kontakt für Datenschutzanfragen: <a href="mailto:felixschmidt0601@web.de">felixschmidt0601@web.de</a></p>

        <h2>6. Aufbewahrungsfristen</h2>
        <p>Konten und Verlaufsdaten werden so lange gespeichert, wie das Konto aktiv ist.
            Nach Kündigung werden Daten innerhalb von [X] Tagen gelöscht.</p>

        <h2>7. Cookies und Sessions</h2>
        <p>ReviewReplyPro verwendet ausschließlich funktional notwendige Session-Cookies.
            Kein Tracking, keine Werbecookies. Cookie-Zustimmung gemäß TTDSG nicht erforderlich
            für technisch notwendige Cookies.</p>

        <?php if (!$legalLive): ?>
        <p class="legal-placeholder-note">
            Diese Datenschutzerklärung enthält noch Platzhalter. Alle eingeklammerten [Angaben]
            und fehlenden Abschnitte müssen vor dem Livegang durch einen Rechtsanwalt geprüft
            und ergänzt werden.
        </p>
        <?php endif; ?>
    </div>

</main>
<?php include ROOT . '/public/_footer.php'; ?>
</body>
</html>
