<?php
/**
 * Auth-Testsuite — läuft gegen die echte Datenbank.
 * Testnutzer werden am Ende automatisch gelöscht.
 */

define('ROOT', dirname(__DIR__));

require ROOT . '/app/Database.php';
require ROOT . '/app/Validator.php';
require ROOT . '/app/Auth/SessionManager.php';
require ROOT . '/app/Auth/UserRepository.php';
require ROOT . '/app/Auth/AuthService.php';

$passed = 0;
$failed = 0;

function ok(string $label, bool $result): void
{
    global $passed, $failed;
    if ($result) {
        echo "\033[32m✓\033[0m $label\n";
        $passed++;
    } else {
        echo "\033[31m✗\033[0m $label\n";
        $failed++;
    }
}

// -------------------------------------------------------
// Hilfsfunktionen
// -------------------------------------------------------
$testPrefix  = 'test_rrp_' . uniqid() . '_';
$testEmail   = $testPrefix . 'owner@example.com';
$testEmail2  = $testPrefix . 'other@example.com';
$testOrg     = 'Testbetrieb ' . uniqid();
$testPw      = 'TestPass123!';

$pdo  = Database::connect();
$repo = new UserRepository($pdo);
$auth = new AuthService($pdo);
$v    = new Validator();

// -------------------------------------------------------
// --- Validator: Registrierung ---
// -------------------------------------------------------
echo "\n\033[1m── Validator: Registrierung ──\033[0m\n";

ok('Leere E-Mail wird abgelehnt', !$v->validateRegisterInput([
    'email' => '', 'password' => 'Test1234!', 'password_confirm' => 'Test1234!', 'org_name' => 'Firma',
]));

ok('Ungültige E-Mail wird abgelehnt', !$v->validateRegisterInput([
    'email' => 'kein-at', 'password' => 'Test1234!', 'password_confirm' => 'Test1234!', 'org_name' => 'Firma',
]));

ok('Passwort unter 8 Zeichen wird abgelehnt', !$v->validateRegisterInput([
    'email' => 'x@y.de', 'password' => 'kurz', 'password_confirm' => 'kurz', 'org_name' => 'Firma',
]));

ok('Abweichende Passwörter werden abgelehnt', !$v->validateRegisterInput([
    'email' => 'x@y.de', 'password' => 'Langes123!', 'password_confirm' => 'Anders123!', 'org_name' => 'Firma',
]));

ok('Zu kurzer Unternehmensname wird abgelehnt', !$v->validateRegisterInput([
    'email' => 'x@y.de', 'password' => 'Test1234!', 'password_confirm' => 'Test1234!', 'org_name' => 'X',
]));

ok('Gültige Registrierungsdaten werden akzeptiert', $v->validateRegisterInput([
    'email' => 'valid@example.com', 'password' => 'Test1234!', 'password_confirm' => 'Test1234!', 'org_name' => 'Meine Firma',
]));

// -------------------------------------------------------
// --- Validator: Login ---
// -------------------------------------------------------
echo "\n\033[1m── Validator: Login ──\033[0m\n";

ok('Leere E-Mail beim Login wird abgelehnt', !$v->validateLoginInput([
    'email' => '', 'password' => 'Test1234!',
]));

ok('Leeres Passwort beim Login wird abgelehnt', !$v->validateLoginInput([
    'email' => 'x@y.de', 'password' => '',
]));

ok('Gültige Login-Daten werden akzeptiert', $v->validateLoginInput([
    'email' => 'user@example.com', 'password' => 'IrgendeinPasswort',
]));

// -------------------------------------------------------
// --- AuthService: Registrierung ---
// -------------------------------------------------------
echo "\n\033[1m── AuthService: Registrierung ──\033[0m\n";

// Session-Simulation: session_start() im CLI nicht sinnvoll,
// daher nur die Datenbankschicht testen ohne SessionManager::setUser()
$hash   = password_hash($testPw, PASSWORD_BCRYPT, ['cost' => 4]);
$userId = $repo->createWithOrg($testOrg, $testEmail, $hash);

ok('Konto + Organisation werden angelegt (userId > 0)', $userId > 0);

$user = $repo->findById($userId);
ok('Benutzer findet sich per ID', $user !== null);
ok('E-Mail stimmt', $user !== null && $user['email'] === $testEmail);
ok('Rolle ist owner', $user !== null && $user['role'] === 'owner');

$orgId = $repo->getOrgId($userId);
ok('org_id ist > 0', $orgId > 0);

// -------------------------------------------------------
// --- UserRepository: findByEmail ---
// -------------------------------------------------------
echo "\n\033[1m── UserRepository ──\033[0m\n";

$found = $repo->findByEmail($testEmail);
ok('findByEmail findet den Testnutzer', $found !== null);
ok('password_hash ist gesetzt', !empty($found['password_hash'] ?? ''));
ok('password_verify funktioniert', $found !== null && password_verify($testPw, $found['password_hash']));

ok('findByEmail gibt null für Unbekannte zurück', $repo->findByEmail('nobody_' . uniqid() . '@example.com') === null);
ok('emailExists gibt true für vorhandene E-Mail zurück', $repo->emailExists($testEmail));
ok('emailExists gibt false für unbekannte E-Mail zurück', !$repo->emailExists('ghost_' . uniqid() . '@example.com'));

// -------------------------------------------------------
// --- AuthService: Doppelte Registrierung ---
// -------------------------------------------------------
echo "\n\033[1m── AuthService: Doppelte E-Mail ──\033[0m\n";

$hash2   = password_hash($testPw, PASSWORD_BCRYPT, ['cost' => 4]);
$userId2 = null;

// Zweiten Nutzer mit selber E-Mail darf nicht entstehen
try {
    $userId2 = $repo->createWithOrg($testOrg . ' Kopie', $testEmail, $hash2);
    ok('Doppelte E-Mail wird abgelehnt (DB-Unique)', false); // sollte nicht hier ankommen
} catch (PDOException $e) {
    ok('Doppelte E-Mail wirft PDOException (Unique-Constraint)', str_contains($e->getMessage(), '1062') || str_contains($e->getCode(), '23'));
}

// emailExists-Prüfung davor (wie AuthService es tut)
ok('emailExists verhindert Doppelregistrierung', $repo->emailExists($testEmail));

// -------------------------------------------------------
// --- AuthService: Passwort-Prüfung ---
// -------------------------------------------------------
echo "\n\033[1m── Auth: Passwort-Verifikation ──\033[0m\n";

ok('Korrektes Passwort wird akzeptiert',
    ($u = $repo->findByEmail($testEmail)) && password_verify($testPw, $u['password_hash']));

ok('Falsches Passwort wird abgelehnt',
    ($u = $repo->findByEmail($testEmail)) && !password_verify('FalschesPasswort!', $u['password_hash']));

// -------------------------------------------------------
// --- Mandantentrennung: Review-Speicherung ---
// -------------------------------------------------------
echo "\n\033[1m── Mandantentrennung ──\033[0m\n";

require ROOT . '/app/ReviewReplyRepository.php';
require ROOT . '/app/ReviewReplyService.php';

$rrRepo = new ReviewReplyRepository($pdo);
$svc    = new ReviewReplyService();

$result = $svc->generate('Tolles Essen!', 'Restaurant', 5, 'freundlich');
$entryId = $rrRepo->save([
    'org_id'      => $orgId,
    'review_text' => 'Tolles Essen!',
    'industry'    => 'Restaurant',
    'stars'       => 5,
    'tone'        => 'freundlich',
    'reply_1'     => $result['reply_1'],
    'reply_2'     => $result['reply_2'],
    'reply_3'     => $result['reply_3'],
    'risk_level'  => $result['risk_level'],
]);

ok('Eintrag wird gespeichert (id > 0)', $entryId > 0);

$history = $rrRepo->getHistory($orgId);
ok('Eigener Verlauf enthält den Eintrag', count($history) >= 1 && $history[0]['id'] === $entryId);

// Zweite Org anlegen und prüfen ob Verlauf leer ist
$hash3   = password_hash($testPw, PASSWORD_BCRYPT, ['cost' => 4]);
$userId3 = $repo->createWithOrg($testOrg . ' Zweite', $testEmail2, $hash3);
$orgId3  = $repo->getOrgId($userId3);
$historyOther = $rrRepo->getHistory($orgId3);
ok('Anderer Mandant sieht keine fremden Einträge', count($historyOther) === 0);

// Löschen mit falscher org_id schlägt fehl
$deletedWrong = $rrRepo->delete($entryId, $orgId3);
ok('Löschen mit fremder org_id schlägt fehl', $deletedWrong === false);

// Löschen mit eigener org_id klappt
$deletedOwn = $rrRepo->delete($entryId, $orgId);
ok('Löschen mit eigener org_id gelingt', $deletedOwn === true);

// -------------------------------------------------------
// --- SessionManager: CSRF ---
// -------------------------------------------------------
echo "\n\033[1m── SessionManager: CSRF ──\033[0m\n";

// Minimale Session-Simulation ohne HTTP-Kontext
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

$token = SessionManager::csrfToken();
ok('CSRF-Token wird generiert (64 Hex-Zeichen)', strlen($token) === 64);
ok('Gleiches Token wird beim zweiten Aufruf zurückgegeben', SessionManager::csrfToken() === $token);
ok('Korrektes Token wird verifiziert', SessionManager::verifyCsrf($token));
ok('Falsches Token wird abgelehnt', !SessionManager::verifyCsrf('falsch'));
ok('Leeres Token wird abgelehnt', !SessionManager::verifyCsrf(''));

// -------------------------------------------------------
// --- Aufräumen ---
// -------------------------------------------------------
// Testnutzer und Orgs löschen (CASCADE löscht review_replies mit)
$pdo->prepare('DELETE FROM users WHERE email LIKE :prefix')->execute([':prefix' => 'test_rrp_%@example.com']);
$pdo->prepare('DELETE FROM organizations WHERE name LIKE :prefix')->execute([':prefix' => 'Testbetrieb %']);

// -------------------------------------------------------
// --- Zusammenfassung ---
// -------------------------------------------------------
echo "\n";
echo "Ergebnis: \033[32m$passed bestanden\033[0m";
if ($failed > 0) {
    echo ", \033[31m$failed fehlgeschlagen\033[0m";
}
echo "\n";
exit($failed > 0 ? 1 : 0);
