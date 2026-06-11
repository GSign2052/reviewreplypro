<?php

require dirname(__DIR__, 3) . '/bootstrap/app.php';

require ROOT . '/app/Database.php';
require ROOT . '/app/Billing/BillingRepository.php';
require ROOT . '/app/Billing/StripeService.php';

// Webhook-Endpoint braucht keine Session/CSRF — signiert mit Webhook-Secret
header('Content-Type: application/json; charset=utf-8');

if (!billingEnabled()) {
    http_response_code(503);
    echo json_encode(['error' => 'Billing disabled']);
    exit;
}

$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (empty($sigHeader)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing signature']);
    exit;
}

try {
    $pdo     = Database::connect();
    $repo    = new BillingRepository($pdo);
    $stripe  = new StripeService($repo);

    $event = $stripe->constructWebhookEvent($payload, $sigHeader);

    // Idempotenz: Duplikat-Events ignorieren
    if ($repo->eventAlreadyProcessed($event->id)) {
        http_response_code(200);
        echo json_encode(['received' => true, 'duplicate' => true]);
        exit;
    }

    $orgId = null;
    $stripe->handleWebhookEvent($event, $orgId);
    $repo->logBillingEvent($event->id, $event->type, $orgId ?: null, (array) $event->data->object);

    http_response_code(200);
    echo json_encode(['received' => true]);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Webhook processing failed']);
}
