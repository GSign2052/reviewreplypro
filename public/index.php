<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReviewReplyPro</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>

<header class="app-header">
    <div class="header-inner">
        <span class="logo">ReviewReply<span class="logo-accent">Pro</span></span>
        <span class="tagline">Professionelle Google-Antworten in Sekunden</span>
    </div>
</header>

<main class="app-main">

    <!-- Linke Seite: Formular -->
    <section class="panel panel-form">
        <h2>Bewertung eingeben</h2>

        <div class="field">
            <label for="review_text">Bewertungstext</label>
            <textarea id="review_text" rows="5" placeholder="Bewertung hier einfügen…" maxlength="2000"></textarea>
            <span class="char-count"><span id="char-count">0</span> / 2000</span>
        </div>

        <div class="field-row">
            <div class="field">
                <label for="industry">Branche</label>
                <select id="industry">
                    <option value="Restaurant">Restaurant</option>
                    <option value="Friseur">Friseur</option>
                    <option value="Kosmetikstudio">Kosmetikstudio</option>
                    <option value="Handwerker">Handwerker</option>
                    <option value="Hotel">Hotel</option>
                    <option value="Fahrschule">Fahrschule</option>
                </select>
            </div>
            <div class="field">
                <label for="tone">Ton</label>
                <select id="tone">
                    <option value="freundlich">Freundlich</option>
                    <option value="professionell">Professionell</option>
                    <option value="entschuldigend">Entschuldigend</option>
                    <option value="premium">Premium</option>
                </select>
            </div>
        </div>

        <div class="field">
            <label>Sterne</label>
            <div class="star-selector" id="star-selector">
                <button class="star-btn" data-value="1">★</button>
                <button class="star-btn" data-value="2">★</button>
                <button class="star-btn" data-value="3">★</button>
                <button class="star-btn" data-value="4">★</button>
                <button class="star-btn" data-value="5">★</button>
            </div>
            <input type="hidden" id="stars" value="0">
        </div>

        <div id="form-errors" class="form-errors hidden"></div>

        <button id="generate-btn" class="btn-primary" disabled>
            <span id="btn-text">Antworten generieren</span>
            <span id="btn-spinner" class="spinner hidden"></span>
        </button>
    </section>

    <!-- Rechte Seite: Ergebnisse -->
    <section class="panel panel-results">
        <h2>Antwortvarianten</h2>

        <div id="risk-badge" class="risk-badge hidden"></div>

        <div id="results-placeholder" class="placeholder">
            <p>Gib links eine Bewertung ein und klicke auf <em>Antworten generieren</em>.</p>
        </div>

        <div id="reply-cards" class="reply-cards hidden">
            <div class="reply-card" data-index="1">
                <div class="reply-card-header">
                    <span class="variant-label">Variante 1</span>
                    <button class="copy-btn" data-target="reply-text-1">Kopieren</button>
                </div>
                <p class="reply-text" id="reply-text-1"></p>
            </div>
            <div class="reply-card" data-index="2">
                <div class="reply-card-header">
                    <span class="variant-label">Variante 2</span>
                    <button class="copy-btn" data-target="reply-text-2">Kopieren</button>
                </div>
                <p class="reply-text" id="reply-text-2"></p>
            </div>
            <div class="reply-card" data-index="3">
                <div class="reply-card-header">
                    <span class="variant-label">Variante 3</span>
                    <button class="copy-btn" data-target="reply-text-3">Kopieren</button>
                </div>
                <p class="reply-text" id="reply-text-3"></p>
            </div>
        </div>
    </section>

</main>

<!-- Verlauf -->
<section class="history-section">
    <div class="history-header">
        <h2>Verlauf</h2>
        <button id="refresh-history" class="btn-ghost">Aktualisieren</button>
    </div>
    <div id="history-list" class="history-list">
        <p class="placeholder">Noch keine Einträge.</p>
    </div>
</section>

<script src="/assets/js/app.js"></script>
</body>
</html>
