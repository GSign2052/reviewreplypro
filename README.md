# ReviewReplyPro

Professionelle Google-Bewertungsantworten für kleine Betriebe in Deutschland. Bewertung eingeben, Branche und Ton wählen, 3 fertige Antwortvarianten erhalten und direkt kopieren.

## Stack

- PHP 8.4
- MySQL 8
- Apache 2.4
- Vanilla JS (kein Framework)

## Lokale Installation

```bash
# 1. Repo klonen
git clone https://github.com/DEIN_USER/reviewreplypro.git /var/www/html

# 2. Datenbank-Config anlegen
cp config/database.example.php config/database.php
# config/database.php mit eigenen Zugangsdaten befüllen

# 3. Schema importieren
mysql -u BENUTZER -p DATENBANKNAME < database/schema.sql

# 4. Tests ausführen
php tests/run.php
```

## VPS-Deployment

```bash
# Apache DocumentRoot auf /var/www/html/public setzen
# config/database.php befüllen
# Schema importieren
# mod_rewrite aktivieren: sudo a2enmod rewrite
```

## Branchen

Restaurant · Friseur · Kosmetikstudio · Handwerker · Hotel · Fahrschule

## Töne

Freundlich · Professionell · Entschuldigend · Premium

## API

| Methode | Pfad | Beschreibung |
|---|---|---|
| POST | `/api/generate-review-reply.php` | 3 Antwortvarianten generieren |
| GET  | `/api/history.php` | Verlauf abrufen (max. 20) |
| POST | `/api/delete-history.php` | Eintrag löschen |
