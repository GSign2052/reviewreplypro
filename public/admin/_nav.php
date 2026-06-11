<header class="app-header">
    <div class="header-inner">
        <a href="/" class="logo-link">
            <span class="logo">ReviewReply<span class="logo-accent">Pro</span></span>
        </a>
        <span class="admin-label">Admin</span>
        <nav class="header-nav">
            <a href="/admin/" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' && strpos($_SERVER['REQUEST_URI'], '/admin') !== false ? 'nav-active' : '' ?>">Übersicht</a>
            <a href="/admin/customers.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'customers.php' ? 'nav-active' : '' ?>">Kunden</a>
            <a href="/admin/billing.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'billing.php' ? 'nav-active' : '' ?>">Billing</a>
            <a href="/admin/system.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'system.php' ? 'nav-active' : '' ?>">System</a>
            <a href="/admin/support.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'support.php' ? 'nav-active' : '' ?>">Support</a>
        </nav>
        <div class="user-nav" style="margin-left:auto">
            <span style="color:var(--muted);font-size:0.8rem"><?= htmlspecialchars($currentUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <a href="/" style="color:var(--accent);font-size:0.8rem;text-decoration:none">← App</a>
        </div>
    </div>
</header>
