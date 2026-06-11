<?php
/**
 * Admin-Zugang: nur owner-Rolle oder ADMIN_EMAIL_ALLOWLIST aus .env
 */

require_once ROOT . '/app/Auth/SessionManager.php';

SessionManager::start();

if (!SessionManager::isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$currentUser  = SessionManager::user();
$isOwner      = ($currentUser['role'] ?? '') === 'owner';
$allowlist    = array_filter(array_map('trim', explode(',', env('ADMIN_EMAIL_ALLOWLIST', ''))));
$isAllowlisted = !empty($allowlist) && in_array($currentUser['email'] ?? '', $allowlist, true);

if (!$isOwner && !$isAllowlisted) {
    http_response_code(403);
    include ROOT . '/public/admin/403.php';
    exit;
}
