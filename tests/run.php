<?php

define('ROOT', dirname(__DIR__));

require ROOT . '/app/Validator.php';
require ROOT . '/app/ReviewReplyService.php';

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

// --- Validator Tests ---
$v = new Validator();

ok('Leerer Text wird abgelehnt', !$v->validateReviewInput([
    'review_text' => '',
    'stars'       => 5,
    'industry'    => 'Restaurant',
    'tone'        => 'freundlich',
]));

ok('Sternezahl 6 wird abgelehnt', !$v->validateReviewInput([
    'review_text' => 'Super Laden!',
    'stars'       => 6,
    'industry'    => 'Restaurant',
    'tone'        => 'freundlich',
]));

ok('Sternezahl 0 wird abgelehnt', !$v->validateReviewInput([
    'review_text' => 'Super Laden!',
    'stars'       => 0,
    'industry'    => 'Restaurant',
    'tone'        => 'freundlich',
]));

ok('Ungültige Branche wird abgelehnt', !$v->validateReviewInput([
    'review_text' => 'Super!',
    'stars'       => 5,
    'industry'    => 'Discounter',
    'tone'        => 'freundlich',
]));

ok('Ungültiger Ton wird abgelehnt', !$v->validateReviewInput([
    'review_text' => 'Super!',
    'stars'       => 5,
    'industry'    => 'Restaurant',
    'tone'        => 'aggressiv',
]));

ok('Gültige Eingabe wird akzeptiert', $v->validateReviewInput([
    'review_text' => 'Super Restaurant!',
    'stars'       => 5,
    'industry'    => 'Restaurant',
    'tone'        => 'freundlich',
]));

// --- Service Tests ---
$svc = new ReviewReplyService();

$r5 = $svc->generate('Tolles Essen!', 'Restaurant', 5, 'freundlich');
ok('5 Sterne → risk_level = low',    $r5['risk_level'] === 'low');
ok('5 Sterne → Antwort enthält Dank', str_contains($r5['reply_1'], 'Dank') || str_contains($r5['reply_1'], 'dank'));
ok('3 Varianten erzeugt bei 5 Sternen', !empty($r5['reply_1']) && !empty($r5['reply_2']) && !empty($r5['reply_3']));

$r3 = $svc->generate('War okay.', 'Hotel', 3, 'professionell');
ok('3 Sterne → risk_level = medium', $r3['risk_level'] === 'medium');

$r1 = $svc->generate('Sehr schlechter Service!', 'Friseur', 1, 'entschuldigend');
ok('1 Stern → risk_level = high',    $r1['risk_level'] === 'high');
ok('1 Stern → Antwort enthält Entschuldigung', str_contains($r1['reply_1'], 'leid') || str_contains($r1['reply_1'], 'bedauern') || str_contains($r1['reply_1'], 'entschuldig'));

$r2 = $svc->generate('Nie wieder.', 'Handwerker', 2, 'professionell');
ok('2 Sterne → risk_level = high',   $r2['risk_level'] === 'high');

$r4 = $svc->generate('Sehr gut!', 'Fahrschule', 4, 'premium');
ok('4 Sterne → risk_level = low',    $r4['risk_level'] === 'low');

// --- Summary ---
echo "\n";
echo "Ergebnis: \033[32m$passed bestanden\033[0m";
if ($failed > 0) {
    echo ", \033[31m$failed fehlgeschlagen\033[0m";
}
echo "\n";
exit($failed > 0 ? 1 : 0);
