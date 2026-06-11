<?php
/**
 * In geschützte JSON-API-Endpunkte einbinden.
 * Prüft Session + CSRF-Token für zustandsändernde Methoden.
 */

require_once ROOT . '/config/security-headers.php';
require_once ROOT . '/app/Auth/SessionManager.php';

SessionManager::start();

if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Nicht angemeldet.']);
    exit;
}

// POST/PUT/DELETE: X-CSRF-Token Header pflicht
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $incoming = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!SessionManager::verifyCsrf($incoming)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'CSRF-Token ungültig.']);
        exit;
    }
}

$currentUser = SessionManager::user();
