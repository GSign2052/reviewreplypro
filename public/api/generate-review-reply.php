<?php

require dirname(__DIR__, 2) . '/bootstrap/app.php';

require ROOT . '/app/Database.php';
require ROOT . '/app/Validator.php';
require ROOT . '/app/ReviewReplyService.php';
require ROOT . '/app/ReviewReplyRepository.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require ROOT . '/app/Middleware/requireAuthApi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültiges JSON.']);
    exit;
}

$validator = new Validator();
if (!$validator->validateReviewInput($body)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $validator->getErrors()]);
    exit;
}

try {
    $service = new ReviewReplyService();
    $result  = $service->generate(
        trim($body['review_text']),
        $body['industry'],
        (int)$body['stars'],
        $body['tone']
    );

    $repo = new ReviewReplyRepository(Database::connect());
    $id   = $repo->save([
        'org_id'      => $currentUser['org_id'],
        'review_text' => trim($body['review_text']),
        'industry'    => $body['industry'],
        'stars'       => (int)$body['stars'],
        'tone'        => $body['tone'],
        'reply_1'     => $result['reply_1'],
        'reply_2'     => $result['reply_2'],
        'reply_3'     => $result['reply_3'],
        'risk_level'  => $result['risk_level'],
    ]);

    echo json_encode([
        'success'    => true,
        'id'         => $id,
        'reply_1'    => $result['reply_1'],
        'reply_2'    => $result['reply_2'],
        'reply_3'    => $result['reply_3'],
        'risk_level' => $result['risk_level'],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    $cfg = require ROOT . '/config/app.php';
    echo json_encode(['success' => false, 'error' => $cfg['debug'] ? $e->getMessage() : 'Serverfehler.']);
}
