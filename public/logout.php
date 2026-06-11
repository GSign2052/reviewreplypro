<?php
require dirname(__DIR__) . '/bootstrap/app.php';

require ROOT . '/app/Auth/SessionManager.php';
require ROOT . '/app/Auth/AuthService.php';
require ROOT . '/app/Database.php';

SessionManager::start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && SessionManager::verifyCsrf($_POST['csrf_token'] ?? '')) {
    $auth = new AuthService(Database::connect());
    $auth->logout();
}

header('Location: /login.php');
exit;
