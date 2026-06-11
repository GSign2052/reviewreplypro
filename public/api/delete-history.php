<?php

define('ROOT', dirname(__DIR__, 2));

require ROOT . '/app/Database.php';
require ROOT . '/app/ReviewReplyRepository.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$id   = isset($body['id']) ? (int)$body['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige ID.']);
    exit;
}

try {
    $repo    = new ReviewReplyRepository(Database::connect());
    $deleted = $repo->delete($id);

    if (!$deleted) {
        http_response_code(404);
        echo json_encode(['error' => 'Eintrag nicht gefunden.']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Serverfehler.']);
}
