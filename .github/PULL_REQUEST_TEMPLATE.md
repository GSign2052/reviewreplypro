## Was ändert dieser PR?

<!-- Kurze Beschreibung der Änderungen -->

## Warum?

<!-- Motivation / verlinktes Issue: Closes #XXX -->

## Art der Änderung

- [ ] Bug Fix
- [ ] Neues Feature
- [ ] Sicherheitsverbesserung
- [ ] Refactoring (kein Funktionsänderung)
- [ ] Dokumentation
- [ ] CI / Tooling

## Checkliste

- [ ] `php tests/run.php` läuft grün
- [ ] `php tests/run_auth.php` läuft grün
- [ ] Kein `config/database.php` oder `.env` committed
- [ ] Keine Passwörter / Secrets im Code
- [ ] `CHANGELOG.md` → `[Unreleased]`-Abschnitt aktualisiert
- [ ] Bei neuen Endpunkten: Auth-Middleware + CSRF-Check eingebaut
- [ ] Bei DB-Änderungen: Migration in `database/` angelegt

## Screenshots (falls UI-Änderung)

<!-- Vorher / Nachher -->
