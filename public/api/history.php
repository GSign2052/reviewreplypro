<?php

require dirname(__DIR__, 2) . '/bootstrap/app.php';

require ROOT . '/app/Database.php';
require ROOT . '/app/ReviewReplyRepository.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require ROOT . '/app/Middleware/requireAuthApi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

try {
    $repo = new ReviewReplyRepository(Database::connect());
    echo json_encode(['success' => true, 'data' => $repo->getHistory($currentUser['org_id'])]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Serverfehler.']);
}
