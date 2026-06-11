# Changelog

Alle nennenswerten Änderungen an diesem Projekt werden hier dokumentiert.

Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).
Versionierung folgt [Semantic Versioning](https://semver.org/lang/de/).

---

## [Unreleased]

### Geplant
- Stripe-Integration (Abo-Modell)
- DSGVO-Modul
- Passwort-Reset per E-Mail
- Team-Accounts (mehrere Nutzer pro Organisation)

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
