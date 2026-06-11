<?php
require dirname(__DIR__) . '/bootstrap/app.php';

require ROOT . '/config/security-headers.php';
require ROOT . '/app/Database.php';
require ROOT . '/app/Auth/SessionManager.php';
require ROOT . '/app/Middleware/requireAuth.php';
require ROOT . '/app/Billing/BillingRepository.php';

$billingEnabled = billingEnabled();
$csrfToken      = SessionManager::csrfToken();
$orgId          = (int) $_SESSION['org_id'];

$plans        = [];
$subscription = false;

if ($billingEnabled) {
    $repo         = new BillingRepository(Database::connect());
    $plans        = $repo->getPlans();
    $subscription = $repo->getSubscriptionByOrg($orgId);
}

$success  = isset($_GET['success']);
$canceled = isset($_GET['canceled']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abo & Rechnung — ReviewReplyPro</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/billing.css">
    <link rel="stylesheet" href="/assets/css/legal.css">
</head>
<body>

<header class="app-header">
    <div class="header-inner">
        <a href="/" class="logo-link">
            <span class="logo">ReviewReply<span class="logo-accent">Pro</span></span>
        </a>
        <nav class="header-nav">
            <a href="/" class="nav-link">App</a>
            <a href="/billing.php" class="nav-link nav-active">Abo & Rechnung</a>
            <a href="/account.php" class="nav-link">Konto</a>
        </nav>
        <div class="user-nav">
            <span><?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?></span>
            <form method="POST" action="/logout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn-logout">Abmelden</button>
            </form>
        </div>
    </div>
</header>

<main class="billing-main">

<?php if (!$billingEnabled): ?>
    <!-- ── Billing-Platzhalter (BILLING_ENABLED=false) ── -->
    <div class="billing-placeholder">
        <span class="badge-placeholder">Noch nicht live</span>
        <h1>Abo & Rechnung</h1>
        <p class="ph-lead">Die Abrechnung wird für den Live-Betrieb vorbereitet.<br>
            Alle Funktionen sind während der Testphase kostenlos nutzbar.</p>

        <div class="ph-plans">
            <div class="ph-plan ph-plan-free">
                <div class="ph-plan-name">Kostenlos</div>
                <div class="ph-plan-price">0 €<span>/Monat</span></div>
                <div class="ph-plan-desc">Bis zu 10 Antworten/Monat</div>
                <span class="ph-plan-badge active">Aktueller Tarif</span>
            </div>
            <div class="ph-plan ph-plan-starter">
                <div class="ph-plan-name">Starter</div>
                <div class="ph-plan-price">9,99 €<span>/Monat</span></div>
                <div class="ph-plan-desc">100 Antworten/Monat, E-Mail-Support</div>
                <span class="ph-plan-badge coming">Demnächst</span>
            </div>
            <div class="ph-plan ph-plan-pro">
                <div class="ph-plan-name">Pro</div>
                <div class="ph-plan-price">24,99 €<span>/Monat</span></div>
                <div class="ph-plan-desc">Unbegrenzt, Priority-Support</div>
                <span class="ph-plan-badge coming">Demnächst</span>
            </div>
        </div>

        <p class="ph-note">Live-Abrechnung folgt nach Freigabe. Kein Handlungsbedarf bis dahin.</p>
    </div>

<?php else: ?>
    <!-- ── Echtes Billing-UI (BILLING_ENABLED=true) ── -->

    <?php if ($success): ?>
        <div class="billing-alert success">Abonnement erfolgreich aktiviert. Danke!</div>
    <?php elseif ($canceled): ?>
        <div class="billing-alert warning">Checkout abgebrochen — kein Abonnement wurde geändert.</div>
    <?php endif; ?>

    <div class="billing-header">
        <h1>Abo & Rechnung</h1>
        <?php if ($subscription): ?>
            <span class="sub-status sub-status-<?= htmlspecialchars($subscription['status'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars(ucfirst($subscription['status']), ENT_QUOTES, 'UTF-8') ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($subscription && $subscription['status'] === 'active'): ?>
        <div class="billing-current">
            <p>Aktueller Tarif: <strong><?= htmlspecialchars($subscription['plan_name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php if ($subscription['current_period_end']): ?>
                <p class="billing-muted">Nächste Abrechnung: <?= date('d.m.Y', strtotime($subscription['current_period_end'])) ?></p>
            <?php endif; ?>
            <button class="btn-primary" id="portal-btn">Rechnungen & Kündigung verwalten</button>
        </div>
    <?php else: ?>
        <div class="plan-grid">
            <?php foreach ($plans as $plan): ?>
                <?php if ($plan['price_cents'] === 0) continue; // kostenlos nicht kaufbar ?>
                <div class="plan-card">
                    <div class="plan-name"><?= htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="plan-price">
                        <?= number_format($plan['price_cents'] / 100, 2, ',', '.') ?> €
                        <span>/<?= htmlspecialchars($plan['interval_'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <p class="plan-desc"><?= htmlspecialchars($plan['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ($plan['stripe_price_id']): ?>
                        <button class="btn-primary checkout-btn"
                                data-price="<?= htmlspecialchars($plan['stripe_price_id'], ENT_QUOTES, 'UTF-8') ?>">
                            Jetzt abonnieren
                        </button>
                    <?php else: ?>
                        <button class="btn-primary" disabled>Demnächst</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>
</main>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

document.querySelectorAll('.checkout-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        btn.textContent = 'Weiterleitung…';
        const res = await fetch('/api/billing/create-checkout-session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
            body: JSON.stringify({ price_id: btn.dataset.price }),
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.url;
        } else {
            alert(data.error || 'Fehler beim Checkout.');
            btn.disabled = false;
            btn.textContent = 'Jetzt abonnieren';
        }
    });
});

const portalBtn = document.getElementById('portal-btn');
if (portalBtn) {
    portalBtn.addEventListener('click', async () => {
        portalBtn.disabled = true;
        portalBtn.textContent = 'Weiterleitung…';
        const res = await fetch('/api/billing/create-portal-session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
            body: '{}',
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.url;
        } else {
            alert(data.error || 'Fehler beim Portal.');
            portalBtn.disabled = false;
            portalBtn.textContent = 'Rechnungen & Kündigung verwalten';
        }
    });
}
</script>
<?php include ROOT . '/public/_footer.php'; ?>
</body>
</html>
