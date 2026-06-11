# Security Policy

## Unterstützte Versionen

| Version | Support            |
|---------|--------------------|
| 1.x     | ✅ Aktiv           |
| < 1.0   | ❌ Kein Support    |

## Sicherheitslücke melden

**Bitte melde Sicherheitslücken NICHT als öffentliches GitHub-Issue.**

Sende stattdessen eine E-Mail an: **felixschmidt0601@web.de**

Bitte füge folgendes bei:
- Beschreibung der Lücke
- Schritte zur Reproduktion
- Mögliche Auswirkung
- Optional: Vorschlag zur Behebung

### Was passiert danach?

1. Eingangsbestätigung innerhalb von 48 Stunden
2. Bewertung und erste Rückmeldung innerhalb von 7 Tagen
3. Fix und koordiniertes Disclosure nach Absprache

## Bekannte Sicherheitsmaßnahmen

Folgendes ist bereits implementiert (Stand v1.1.0):

- CSRF-Token (64-Byte Hex, `hash_equals()`) auf allen POST/DELETE-Endpunkten
- `password_hash()` mit BCRYPT cost=12
- Session-Regenerierung bei Login (`session_regenerate_id(true)`)
- Inaktivitäts-Timeout nach 2 Stunden
- Mandantentrennung über `org_id` auf Datenbankebene
- Content-Security-Policy (`default-src 'self'`)
- Strict-Transport-Security (1 Jahr, inkl. Subdomains)
- Cookies: `HttpOnly`, `SameSite=Lax`, `Secure` (HTTPS)
- Ausschließlich PDO Prepared Statements
- `htmlspecialchars()` auf allen Ausgaben

## Scope (außerhalb des Scopes)

- Denial-of-Service-Angriffe (kein Rate-Limiting implementiert — bekannte Lücke)
- Social Engineering
- Angriffe auf die Infrastruktur/Hosting-Umgebung
