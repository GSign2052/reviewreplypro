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
    <title>Impressum — ReviewReplyPro</title>
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
            Platzhalter — Vor Livegang final prüfen und durch echte Angaben ersetzen
        </div>
    <?php endif; ?>

    <h1>Impressum</h1>
    <p class="legal-last-updated">Stand: <?= date('d.m.Y') ?></p>

    <?php if ($legalLive): ?>
        <!-- HIER: echtes Impressum einfügen (Name, Anschrift, USt-ID, etc.) -->
        <p>Angaben gemäß § 5 TMG:</p>
        <p>[Name / Firma]<br>[Adresse]<br>[PLZ Ort]</p>
        <p><strong>Kontakt:</strong><br>
        E-Mail: felixschmidt0601@web.de</p>
    <?php else: ?>
        <div class="legal-placeholder-content">
            <h2>Angaben gemäß § 5 TMG</h2>
            <p>[Vollständiger Name / Firma]<br>
            [Straße, Hausnummer]<br>
            [PLZ] [Ort]</p>

            <h2>Kontakt</h2>
            <p>E-Mail: felixschmidt0601@web.de</p>

            <h2>Umsatzsteuer-Identifikationsnummer</h2>
            <p>[USt-IdNr. gemäß § 27a UStG, falls vorhanden]</p>

            <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
            <p>[Name und Anschrift des Verantwortlichen]</p>

            <p class="legal-placeholder-note">
                Diese Seite enthält noch Platzhalter. Alle eingeklammerten [Angaben]
                müssen vor dem Livegang durch korrekte Informationen ersetzt werden.
                Pflichtangaben laut § 5 TMG und § 55 RStV.
            </p>
        </div>
    <?php endif; ?>

</main>
<?php include ROOT . '/public/_footer.php'; ?>
</body>
</html>
