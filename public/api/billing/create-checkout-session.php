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

$body = json_decode(file_get_contents('php://input'), true);
$stripePriceId = trim($body['price_id'] ?? '');

if (!preg_match('/^price_[a-zA-Z0-9]+$/', $stripePriceId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültige price_id']);
    exit;
}

try {
    $pdo     = Database::connect();
    $repo    = new BillingRepository($pdo);
    $stripe  = new StripeService($repo);
    $orgId   = (int) $_SESSION['org_id'];
    $email   = $_SESSION['email'] ?? '';
    $appUrl  = rtrim(env('APP_URL', 'http://localhost'), '/');

    $checkoutUrl = $stripe->createCheckoutSession(
        $orgId,
        $email,
        $stripePriceId,
        $appUrl . '/billing.php?success=1',
        $appUrl . '/billing.php?canceled=1',
    );

    echo json_encode(['success' => true, 'url' => $checkoutUrl]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Interner Fehler beim Checkout.']);
}
