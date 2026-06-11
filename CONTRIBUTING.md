# Contributing

Danke für dein Interesse an ReviewReplyPro!

## Wie du beitragen kannst

### Bug melden
Nutze das [Bug-Report-Template](.github/ISSUE_TEMPLATE/bug_report.yml). Je mehr Details, desto schneller der Fix.

### Feature vorschlagen
Nutze das [Feature-Request-Template](.github/ISSUE_TEMPLATE/feature_request.yml). Erkläre den Anwendungsfall — nicht nur die Funktion.

### Code beisteuern

```bash
# 1. Fork auf GitHub anlegen
# 2. Klonen
git clone https://github.com/DEIN_USER/reviewreplypro.git
cd reviewreplypro

# 3. Feature-Branch anlegen
git checkout -b feature/dein-feature-name

# 4. Änderungen machen, Tests schreiben
php tests/run.php
php tests/run_auth.php

# 5. Commit
git commit -m "feat: kurze Beschreibung was und warum"

# 6. Push + Pull Request
git push origin feature/dein-feature-name
```

## Commit-Nachrichten

Wir nutzen [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: neues Feature
fix: Bugfix
security: Sicherheitsverbesserung
refactor: Umbau ohne Funktionsänderung
test: Tests hinzufügen oder ändern
docs: nur Dokumentation
chore: Tooling, Build, CI
```

## Code-Stil

- PHP: PSR-12 orientiert, kein Framework
- Kein ORM — PDO Prepared Statements direkt
- Neue Backend-Funktionen bekommen Tests in `tests/`
- Keine Kommentare die erklären WAS der Code tut — nur WARUM (nicht-offensichtliche Entscheidungen)
- Sicherheit first: alle Nutzereingaben validieren, alle Ausgaben escapen

## Pull Request Checkliste

- [ ] Tests laufen grün durch (`php tests/run.php && php tests/run_auth.php`)
- [ ] Kein `config/database.php` committed
- [ ] Keine Passwörter oder Secrets im Code
- [ ] CHANGELOG.md aktualisiert (Abschnitt `[Unreleased]`)
- [ ] PR-Beschreibung erklärt das Warum

## Fragen?

Einfach ein Issue öffnen mit dem Label `question`.
