<?php
/**
 * Sicherheits-Header für alle Antworten.
 * X-Content-Type-Options, X-Frame-Options und Referrer-Policy
 * werden bereits von Apaches security.conf gesetzt — hier nur Ergänzungen.
 */

header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self'; " .
    "style-src 'self' 'unsafe-inline'; " .   // inline <style>-Blöcke erlaubt
    "img-src 'self' data:; " .
    "font-src 'self'; " .
    "connect-src 'self'; " .
    "frame-ancestors 'none';"
);

// HSTS: nur über HTTPS aktivieren (nicht im lokalen Dev-HTTP)
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
