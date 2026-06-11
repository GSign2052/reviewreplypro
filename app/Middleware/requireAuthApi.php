<?php
/**
 * In geschützte JSON-API-Endpunkte einbinden.
 * Gibt 401 zurück wenn keine gültige Session vorhanden.
 */

require_once ROOT . '/app/Auth/SessionManager.php';

SessionManager::start();

if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Nicht angemeldet.']);
    exit;
}

$currentUser = SessionManager::user();
