<?php

use Dotenv\Dotenv;

define('ROOT', dirname(__DIR__));

// Composer Autoloader
require ROOT . '/vendor/autoload.php';

// .env laden (tolerant: kein Fehler wenn .env fehlt — CI baut config/database.php)
$dotenv = Dotenv::createImmutable(ROOT, '.env');
$dotenv->safeLoad();

// Kern-App-Config als globales Array
$GLOBALS['cfg'] = [
    'env'   => $_ENV['APP_ENV']  ?? getenv('APP_ENV')  ?: 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN),
    'url'   => $_ENV['APP_URL']  ?? getenv('APP_URL')  ?: '',
    'name'  => $_ENV['APP_NAME'] ?? getenv('APP_NAME') ?: 'ReviewReplyPro',
];

// PHP-Fehlerausgabe nur im Debug-Modus
if ($GLOBALS['cfg']['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Hilfsfunktion: ENV-Wert lesen mit Fallback
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return ($value !== false && $value !== null) ? $value : $default;
}

// Hilfsfunktion: Billing aktiv?
function billingEnabled(): bool
{
    return filter_var(env('BILLING_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
}

// Hilfsfunktion: aktuelles Environment
function appEnv(): string
{
    return $GLOBALS['cfg']['env'];
}

// Hilfsfunktion: Ist rechtlicher Inhalt live?
function legalLive(): bool
{
    return filter_var(env('LEGAL_MODE', false), FILTER_VALIDATE_BOOLEAN);
}
