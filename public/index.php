<?php
define('ROOT', dirname(__DIR__));

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Auth/SessionManager.php';
require ROOT . '/app/Middleware/requireAuth.php';

$csrfToken = SessionManager::csrfToken();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReviewReplyPro</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        .user-nav {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.82rem;
            color: var(--muted);
        }
        .user-nav span { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-nav form { margin: 0; }
        .btn-logout {
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--muted);
            padding: 0.3rem 0.75rem;
            font-size: 0.8rem;
            cursor: pointer;
            transition: border-color 0.15s, color 0.15s;
        }
        .btn-logout:hover { border-color: var(--accent); color: var(--text); }
    </style>
</head>
<body>

<!-- Onboarding Modal -->
<div class="modal-overlay hidden" id="onboarding-modal" role="dialog" aria-modal="true" aria-labelledby="onboarding-title">
    <div class="modal-card">
        <h2 class="modal-title" id="onboarding-title">Willkommen bei ReviewReplyPro</h2>
        <div class="modal-body">
            <div class="modal-row">
                <span class="modal-icon">✍️</span>
                <p class="modal-text"><strong>Was das Tool macht:</strong> Du gibst eine Google-Bewertung ein — das Tool erzeugt drei formulierte Antwortvarianten passend zu deinem Betrieb.</p>
            </div>
            <div class="modal-row">
                <span class="modal-icon">🚫</span>
                <p class="modal-text"><strong>Was es bewusst nicht tut:</strong> Keine automatische Veröffentlichung. Antworten werden nur generiert, nie direkt gepostet.</p>
            </div>
            <div class="modal-row">
                <span class="modal-icon">👁️</span>
                <p class="modal-text"><strong>Warum immer kurz prüfen:</strong> Diese Antworten gehören zu deiner Marke — ein kurzer Blick vor dem Kopieren schützt vor ungenauen Formulierungen.</p>
            </div>
        </div>
        <button class="modal-btn" id="onboarding-close">Verstanden — loslegen</button>
    </div>
</div>

<!-- History Detail Modal -->
<div class="modal-overlay hidden" id="history-modal" role="dialog" aria-modal="true">
    <div class="modal-card hist-modal-card">
        <div class="modal-close-row">
            <span class="modal-close-label">Gespeicherter Eintrag</span>
            <button class="modal-close-btn" id="history-modal-close" aria-label="Schließen">✕</button>
        </div>
        <div id="hist-modal-meta" class="hist-modal-meta"></div>
        <div id="hist-modal-review" class="hist-modal-review"></div>
        <div id="hist-modal-replies" class="hist-modal-replies"></div>
    </div>
</div>

<header class="app-header">
    <div class="header-inner">
        <span class="logo">ReviewReply<span class="logo-accent">Pro</span></span>
        <span class="tagline">Professionelle Google-Antworten in Sekunden</span>
        <div class="user-nav">
            <span title="<?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <form method="POST" action="/logout.php">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn-logout">Abmelden</button>
            </form>
        </div>
    </div>
</header>

<!-- So funktioniert's -->
<div class="how-it-works">
    <div class="hiw-inner">
        <div class="hiw-step">
            <span class="hiw-num">1</span>
            <span class="hiw-label">Bewertung einfügen</span>
        </div>
        <span class="hiw-arrow">›</span>
        <div class="hiw-step">
            <span class="hiw-num">2</span>
            <span class="hiw-label">Ton &amp; Sterne wählen</span>
        </div>
        <span class="hiw-arrow">›</span>
        <div class="hiw-step">
            <span class="hiw-num">3</span>
            <span class="hiw-label">Antwort kopieren &amp; prüfen</span>
        </div>
    </div>
</div>

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
                <div class="field-label-row">
                    <label for="industry">Branche</label>
                    <span class="tip-wrap"><span class="tip-icon" tabindex="0">?</span><span class="tip-box">Beeinflusst Formulierungen und Tonalität – z.&nbsp;B. unterscheidet sich ein Restaurant von einem Handwerksbetrieb.</span></span>
                </div>
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
                <div class="field-label-row">
                    <label for="tone">Ton</label>
                    <span class="tip-wrap"><span class="tip-icon" tabindex="0">?</span><span class="tip-box">Steuert den Stil aller drei Antwortvarianten. <em>Freundlich</em> = herzlich &amp; direkt. <em>Professionell</em> = sachlich &amp; distanziert. <em>Entschuldigend</em> = deeskalierend.</span></span>
                </div>
                <select id="tone">
                    <option value="freundlich">Freundlich</option>
                    <option value="professionell">Professionell</option>
                    <option value="entschuldigend">Entschuldigend</option>
                    <option value="premium">Premium</option>
                </select>
            </div>
        </div>

        <div class="field">
            <div class="field-label-row">
                <label>Sterne</label>
                <span class="tip-wrap"><span class="tip-icon" tabindex="0">?</span><span class="tip-box">Die Sternanzahl beeinflusst den Risikohinweis. 1–2 Sterne = erhöhte Vorsicht empfohlen.</span></span>
            </div>
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

        <div id="risk-wrap" class="risk-wrap hidden">
            <span id="risk-badge" class="risk-badge"></span>
            <span id="risk-sub" class="risk-sub"></span>
        </div>

        <div id="results-placeholder" class="placeholder">
            <p>Gib links eine Bewertung ein und klicke auf <em>Antworten generieren</em>.</p>
        </div>

        <div id="reply-cards" class="reply-cards hidden">
            <div class="reply-card" data-index="1">
                <div class="reply-card-header">
                    <div class="variant-meta">
                        <span class="variant-name" id="variant-name-1">Variante 1</span>
                        <span class="variant-hint" id="variant-hint-1"></span>
                    </div>
                    <button class="copy-btn" data-target="reply-text-1">Kopieren</button>
                </div>
                <p class="reply-text" id="reply-text-1"></p>
            </div>
            <div class="reply-card" data-index="2">
                <div class="reply-card-header">
                    <div class="variant-meta">
                        <span class="variant-name" id="variant-name-2">Variante 2</span>
                        <span class="variant-hint" id="variant-hint-2"></span>
                    </div>
                    <button class="copy-btn" data-target="reply-text-2">Kopieren</button>
                </div>
                <p class="reply-text" id="reply-text-2"></p>
            </div>
            <div class="reply-card" data-index="3">
                <div class="reply-card-header">
                    <div class="variant-meta">
                        <span class="variant-name" id="variant-name-3">Variante 3</span>
                        <span class="variant-hint" id="variant-hint-3"></span>
                    </div>
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
