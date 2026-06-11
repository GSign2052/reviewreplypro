<?php

require dirname(__DIR__, 3) . '/bootstrap/app.php';

require ROOT . '/app/Database.php';
require ROOT . '/app/Auth/SessionManager.php';
require ROOT . '/app/Middleware/requireAuthApi.php';
require ROOT . '/app/Billing/BillingRepository.php';
require ROOT . '/app/Billing/StripeService.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!billingEnabled()) {
    http_response_code(503);
    echo json_encode(['success' => false, 'error' => 'Billing ist noch nicht freigeschaltet.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $pdo     = Database::connect();
    $repo    = new BillingRepository($pdo);
    $stripe  = new StripeService($repo);
    $orgId   = (int) $_SESSION['org_id'];
    $appUrl  = rtrim(env('APP_URL', 'http://localhost'), '/');

    $portalUrl = $stripe->createPortalSession($orgId, $appUrl . '/billing.php');
    echo json_encode(['success' => true, 'url' => $portalUrl]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Interner Fehler beim Portal.']);
}
