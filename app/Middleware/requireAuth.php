<?php
/**
 * In geschützte HTML-Seiten einbinden.
 * Leitet unauthentifizierte Nutzer zum Login um.
 */

require_once ROOT . '/app/Auth/SessionManager.php';

SessionManager::start();

if (!SessionManager::isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$currentUser = SessionManager::user();
