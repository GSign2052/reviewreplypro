# ReviewReplyPro

> Professionelle Google-Bewertungsantworten für kleine Betriebe — in Sekunden, auf Deutsch.

[![CI](https://github.com/GSign2052/reviewreplypro/actions/workflows/ci.yml/badge.svg)](https://github.com/GSign2052/reviewreplypro/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Was ist ReviewReplyPro?

Kleine Betriebe (Restaurants, Friseure, Handwerker, Hotels …) bekommen täglich Google-Bewertungen — und haben selten Zeit, auf jede professionell zu antworten. ReviewReplyPro löst das: Bewertungstext einfügen, Branche und Ton wählen, drei formulierte Antwortvarianten erhalten und direkt kopieren.

**Bewusst kein Auto-Posting.** Die Antworten werden nur generiert, nie automatisch veröffentlicht. Der Betreiber prüft und postet selbst.

---

## Features

- **3 Antwortvarianten** pro Bewertung: Kurz & prägnant · Ausgewogen · Ausführlich
- **Risikohinweis** (niedrig / mittel / hoch) basierend auf Sternanzahl und Inhalt
- **Ton-Steuerung**: Freundlich · Professionell · Entschuldigend · Premium
- **6 Branchen**: Restaurant · Friseur · Kosmetikstudio · Handwerker · Hotel · Fahrschule
- **Multi-Tenant**: Jedes Konto sieht nur seine eigenen Daten (org_id-Trennung auf DB-Ebene)
- **Onboarding-Modal** beim ersten Login mit Nutzungshinweisen
- **Verlauf** mit Detail-Modal (Original + alle 3 Varianten, klickbar)
- **CSRF-Schutz** auf allen zustandsändernden Endpunkten
- **Security-Header**: CSP, HSTS, Permissions-Policy
- **56 automatisierte Tests** (Unit + Integration)

---

## Architektur

```
reviewreplypro/
├── app/
│   ├── Auth/
│   │   ├── AuthService.php        # Login / Register / Logout
│   │   ├── SessionManager.php     # Sessions, CSRF-Token, Inactivity-Timeout
│   │   └── UserRepository.php     # DB-Zugriff Users + Organizations
│   ├── Middleware/
│   │   ├── requireAuth.php        # HTML-Seiten-Guard (→ /login.php)
│   │   └── requireAuthApi.php     # API-Guard (→ 401/403 JSON) + CSRF-Check
│   ├── Database.php               # PDO-Singleton
│   ├── ReviewReplyRepository.php  # CRUD review_replies (org_id-isoliert)
│   ├── ReviewReplyService.php     # KI-Logik: Prompt → 3 Varianten + Risiko
│   └── Validator.php              # Eingabevalidierung (Register, Login, Review)
├── assets/
│   ├── css/main.css
│   └── js/app.js                  # Vanilla JS, kein Framework
├── config/
│   ├── app.php                    # App-Konfiguration (env, debug, url)
│   ├── database.php               # ⚠️ gitignored — lokal anlegen
│   ├── database.example.php       # Vorlage für database.php
│   └── security-headers.php      # CSP, HSTS, Permissions-Policy
├── database/
│   ├── schema.sql                 # Initiales DB-Schema
│   └── migrate_auth.sql           # Migration: Multi-Tenant (orgs + users + org_id)
├── public/                        # DocumentRoot des Webservers
│   ├── api/
│   │   ├── generate-review-reply.php
│   │   ├── history.php
│   │   └── delete-history.php
│   ├── index.php                  # Hauptapp (Auth-geschützt)
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── .htaccess
└── tests/
    ├── run.php                    # Unit-Tests (16)
    └── run_auth.php               # Auth + Tenant-Isolation + CSRF (40)
```

### Datenbankschema

```
organizations  ──┐
                 │ 1:n
users          ──┘  (org_id FK)
                 │ 1:n
review_replies ──┘  (org_id FK, CASCADE DELETE)
```

Alle Datenbankabfragen auf `review_replies` filtern zwingend nach `org_id`. DELETE prüft zusätzlich die Ownership. Session speichert `user_id` + `org_id`.

---

## Lokale Entwicklungsumgebung

### Voraussetzungen

- PHP 8.4+ mit Erweiterungen: `pdo`, `pdo_mysql`
- MySQL 8.0+
- Apache 2.4 mit `mod_rewrite`

### Setup

```bash
# 1. Repo klonen
git clone https://github.com/GSign2052/reviewreplypro.git
cd reviewreplypro

# 2. Datenbank-Config anlegen
cp config/database.example.php config/database.php
# config/database.php mit deinen Zugangsdaten befüllen

# 3. Datenbank und User anlegen (MySQL)
mysql -u root -p <<'SQL'
CREATE DATABASE reviewreplypro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rrpuser'@'localhost' IDENTIFIED BY 'sicheres_passwort_hier';
GRANT ALL ON reviewreplypro.* TO 'rrpuser'@'localhost';
SQL

# 4. Schema importieren
mysql -u rrpuser -p reviewreplypro < database/schema.sql
mysql -u rrpuser -p reviewreplypro < database/migrate_auth.sql

# 5. Apache vHost (DocumentRoot auf /public/)
sudo nano /etc/apache2/sites-available/reviewreplypro.conf
```

Apache vHost:

```apache
<VirtualHost *:80>
    ServerName dev.local
    DocumentRoot /var/www/html/public
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
sudo a2ensite reviewreplypro
sudo a2enmod rewrite
sudo systemctl reload apache2

# /etc/hosts
echo "127.0.0.1  dev.local" | sudo tee -a /etc/hosts

# 6. Tests ausführen
php tests/run.php
php tests/run_auth.php
```

---

## Produktions-Deployment (VPS)

```bash
# 1. Repo auf Server klonen
git clone https://github.com/GSign2052/reviewreplypro.git /var/www/reviewreplypro

# 2. config/database.php anlegen (NICHT committen!)
cp config/database.example.php config/database.php
nano config/database.php

# 3. config/app.php für Produktion anpassen
#    env: 'production', debug: false, url: 'https://deinedomain.de'

# 4. Schema importieren
mysql -u rrpuser -p reviewreplypro < database/schema.sql
mysql -u rrpuser -p reviewreplypro < database/migrate_auth.sql

# 5. Apache: DocumentRoot auf /var/www/reviewreplypro/public
# 6. SSL mit Let's Encrypt (certbot)
sudo certbot --apache -d deinedomain.de

# 7. Permissions
sudo chown -R www-data:www-data /var/www/reviewreplypro
sudo chmod -R 755 /var/www/reviewreplypro
sudo chmod 640 /var/www/reviewreplypro/config/database.php
```

### Updates einspielen

```bash
cd /var/www/reviewreplypro
git pull origin main
# Neue Migrations ggf. einspielen:
# mysql -u rrpuser -p reviewreplypro < database/migrate_XYZ.sql
```

---

## API-Referenz

Alle Endpunkte erfordern eine aktive Session. POST/DELETE erfordern zusätzlich den CSRF-Token im Header `X-CSRF-Token`.

| Methode | Pfad                              | Auth | CSRF | Beschreibung                        |
|---------|-----------------------------------|------|------|-------------------------------------|
| `POST`  | `/api/generate-review-reply.php`  | ✓    | ✓    | 3 Antwortvarianten + Risiko-Level   |
| `GET`   | `/api/history.php`                | ✓    | —    | Verlauf (max. 20, eigene Org)       |
| `POST`  | `/api/delete-history.php`         | ✓    | ✓    | Eintrag löschen (nur eigene Org)    |

### POST /api/generate-review-reply.php

Request:
```json
{
  "review_text": "Tolles Essen, aber der Service war sehr langsam.",
  "industry": "Restaurant",
  "stars": 3,
  "tone": "freundlich"
}
```

Response `200`:
```json
{
  "success": true,
  "reply_1": "Vielen Dank für Ihre ehrliche Bewertung ...",
  "reply_2": "Wir schätzen Ihr Feedback ...",
  "reply_3": "Herzlichen Dank für Ihren Besuch ...",
  "risk_level": "medium"
}
```

Fehlercodes: `401` (nicht eingeloggt) · `403` (CSRF-Token ungültig) · `422` (Validierungsfehler)

---

## Sicherheit

| Maßnahme | Implementierung |
|---|---|
| Passwörter | `password_hash()` BCRYPT cost=12 |
| Sessions | `session_regenerate_id(true)` bei Login; Inaktivitäts-Timeout 2h |
| CSRF | 64-Hex-Token in Session, `X-CSRF-Token`-Header, `hash_equals()` |
| Mandantentrennung | Alle Queries filtern nach `org_id`; DELETE prüft Ownership |
| SQL-Injection | Ausschließlich PDO Prepared Statements |
| XSS | `htmlspecialchars()` auf allen Ausgaben |
| Content-Security-Policy | `default-src 'self'` mit minimalen Ausnahmen |
| HSTS | 1 Jahr, inkl. Subdomains (nur HTTPS) |
| Cookies | `HttpOnly`, `SameSite=Lax`, `Secure` (HTTPS) |

Sicherheitslücken bitte **nicht** öffentlich als Issue melden — siehe [SECURITY.md](SECURITY.md).

---

## Tests ausführen

```bash
# Unit-Tests (ReviewReplyService: Validierung, Risiko-Logik)
php tests/run.php

# Auth + Integration-Tests (Validator, DB, CSRF, Mandantentrennung)
php tests/run_auth.php
```

Erwartete Ausgabe:
```
Ergebnis: 16 bestanden
Ergebnis: 40 bestanden
```

Tests laufen gegen die echte Datenbank. Testnutzer werden am Ende automatisch gelöscht.

---

## Roadmap

- [ ] **Stripe-Integration** — Abo-Modell (Checkout + Customer Portal)
- [ ] **DSGVO-Modul** — Datenschutzerklärung, Löschanfragen, Cookie-Banner
- [ ] **Mehr Branchen** — Arztpraxis, Apotheke, Autowerkstatt, Zahnarzt
- [ ] **Passwort-Reset** — E-Mail-Flow mit sicherem Token
- [ ] **Team-Accounts** — mehrere Nutzer pro Organisation
- [ ] **Webhook-Integration** — direkte Anbindung an Google Business Profile API
- [ ] **Export** — Verlauf als CSV/PDF

---

## Contributing

Beiträge sind willkommen! Bitte zuerst [CONTRIBUTING.md](CONTRIBUTING.md) lesen.

---

## Lizenz

[MIT](LICENSE) © 2026 GSign2052
