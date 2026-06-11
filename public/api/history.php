<?php

define('ROOT', dirname(__DIR__, 2));

require ROOT . '/app/Database.php';
require ROOT . '/app/ReviewReplyRepository.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

try {
    $repo    = new ReviewReplyRepository(Database::connect());
    $history = $repo->getHistory(20);
    echo json_encode(['data' => $history]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Serverfehler.']);
}
