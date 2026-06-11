# Changelog

Alle nennenswerten Änderungen an diesem Projekt werden hier dokumentiert.

Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).
Versionierung folgt [Semantic Versioning](https://semver.org/lang/de/).

---

## [Unreleased]

### Hinzugefügt
- **Composer + phpdotenv**: Konfiguration aus `.env` geladen; Fallback auf `config/database.php` für CI
- **Bootstrap** `bootstrap/app.php`: zentraler App-Einstieg, globale Hilfsfunktionen `env()`, `billingEnabled()`, `legalLive()`, `appEnv()`
- **floating-ui**: Tooltips an Branche/Ton/Sterne positionierungssicher; geführter 3-Schritt-Onboarding-Popover
- **Stripe/Billing**: `app/Billing/StripeService.php`, `BillingRepository.php`, DB-Migration `migrate_billing.sql`
- Billing-Seite `/billing.php` mit Feature-Flag (`BILLING_ENABLED`): Platzhalter-UI wenn false, echte Preispläne wenn true
- API-Endpunkte: `/api/billing/create-checkout-session.php`, `/api/billing/create-portal-session.php`, `/api/webhooks/stripe.php` mit Idempotenz
- **Admin-Bereich** `/admin/`: Übersicht, Kunden, Billing-Status, System-Info, Support — nur owner/Allowlist
- Live-Checkliste im Admin: alle Voraussetzungen auf einen Blick
- **Rechtliche Platzhalter**: `/impressum.php`, `/datenschutz.php`, Konto-Seite `/account.php` mit DSGVO-Hinweisen
- Footer mit Impressum/Datenschutz/Kontakt-Links auf allen Seiten
- **CI-Fix**: PHP-Syntax-Check schlägt jetzt wirklich fehl bei Syntaxfehlern (|| true entfernt)
- **Tag-Fix**: v1.0.1 als Git-Tag nachgetragen (war in CHANGELOG, aber nicht getaggt)
- `.env.example` mit vollständiger Konfigurationsdokumentation

### Geplant
- Passwort-Reset per E-Mail
- Konto-Löschung und Daten-Export (DSGVO)
- Team-Accounts (mehrere Nutzer pro Organisation)
- Rate-Limiting

---

## [1.2.0] — 2026-06-11

### Hinzugefügt
- **Onboarding-Modal** beim ersten Login (einmalig, localStorage-gesteuert)
- **„So funktioniert's"-Strip** mit 3 nummerierten Schritten oberhalb des Formulars
- **Tooltip-System** (`?`-Icons) an Feldern Branche, Ton und Sterne
- **Varianten-Labels**: Kurz & prägnant / Ausgewogen / Ausführlich mit kurzem Nutzungshinweis
- **Risk-Badge-Erklärung**: erläuternder Untertitel unter dem Risikohinweis
- **History-Detail-Modal**: Klick auf Verlaufseintrag zeigt Original-Bewertung + alle 3 Antworten
- Leerer Verlauf zeigt jetzt eine Handlungsaufforderung

---

## [1.1.0] — 2026-06-11

### Hinzugefügt
- **CSRF-Schutz** auf allen JSON-API-Endpunkten (X-CSRF-Token-Header)
- `<meta name="csrf-token">` in index.php für Frontend-Zugriff
- **Security-Header**: Content-Security-Policy, Strict-Transport-Security, Permissions-Policy
- `config/security-headers.php` zentral für alle Seiten

### Geändert
- `requireAuthApi.php`: prüft jetzt bei POST/DELETE den CSRF-Token
- `app.js`: sendet `X-CSRF-Token`-Header bei generate und delete

### Sicherheit
- **HOCH behoben**: JSON-APIs akzeptierten POST ohne CSRF-Token
- **MITTEL behoben**: Fehlende CSP- und HSTS-Header

---

## [1.0.1] — 2026-06-10

### Geändert
- Mindestlänge für Bewertungstext auf 10 Zeichen erhöht
- API-Response enthält jetzt immer `success`-Feld

---

## [1.0.0] — 2026-06-10

### Hinzugefügt
- **Multi-Tenant-Auth**: Registrierung, Login, Logout mit Organisations-Trennung
- `organizations`- und `users`-Tabellen mit FK-Beziehung
- `org_id` auf `review_replies` für Mandantentrennung
- `SessionManager`: CSRF-Token, Inaktivitäts-Timeout (2h), `session_regenerate_id()`
- `AuthService`, `UserRepository` für Registrierung und Login
- `requireAuth.php` und `requireAuthApi.php` als Middleware
- Registrierungs- und Login-Seite im Dark-Theme
- 56 automatisierte Tests (Unit + Auth + CSRF + Mandantentrennung)

---

## [0.1.0] — 2026-06-09

### Hinzugefügt
- Erstes Release: Review eingeben, 3 Antwortvarianten generieren, kopieren
- Verlauf mit Löschfunktion
- 6 Branchen, 4 Töne, 5-Sterne-Auswahl
- Risiko-Level (low / medium / high) basierend auf Sternanzahl

[Unreleased]: https://github.com/GSign2052/reviewreplypro/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/GSign2052/reviewreplypro/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/GSign2052/reviewreplypro/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/GSign2052/reviewreplypro/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/GSign2052/reviewreplypro/compare/v0.1.0...v1.0.0
[0.1.0]: https://github.com/GSign2052/reviewreplypro/releases/tag/v0.1.0
