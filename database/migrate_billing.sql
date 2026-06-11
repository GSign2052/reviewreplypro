-- ── Billing Migration ────────────────────────────────────────────────────────
-- Stripe-Abo-Modell: subscription_plans, subscriptions, stripe_customers, billing_events
-- Alle Tabellen organisationsbezogen (org_id als Mandanten-Schlüssel)

-- Verfügbare Tarife (zentral gepflegt, nicht pro Mandant)
CREATE TABLE IF NOT EXISTS subscription_plans (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(60) NOT NULL UNIQUE,       -- z.B. 'starter', 'pro', 'agency'
    name         VARCHAR(100) NOT NULL,
    description  TEXT,
    price_cents  INT UNSIGNED NOT NULL DEFAULT 0,   -- Preis in Cent (EUR)
    currency     CHAR(3) NOT NULL DEFAULT 'EUR',
    interval_    VARCHAR(20) NOT NULL DEFAULT 'month', -- month | year
    stripe_price_id VARCHAR(120),                   -- Stripe Price ID (live/test)
    active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stripe Customer-Mapping (1 Eintrag pro Organisation)
CREATE TABLE IF NOT EXISTS stripe_customers (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    org_id             INT UNSIGNED NOT NULL UNIQUE,
    stripe_customer_id VARCHAR(120) NOT NULL,
    email              VARCHAR(255),
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Aktive/historische Abonnements
CREATE TABLE IF NOT EXISTS subscriptions (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    org_id                INT UNSIGNED NOT NULL,
    plan_id               INT UNSIGNED NOT NULL,
    stripe_subscription_id VARCHAR(120) UNIQUE,
    status                ENUM('trialing','active','past_due','canceled','incomplete','paused') NOT NULL DEFAULT 'incomplete',
    current_period_start  TIMESTAMP NULL,
    current_period_end    TIMESTAMP NULL,
    cancel_at_period_end  TINYINT(1) NOT NULL DEFAULT 0,
    trial_ends_at         TIMESTAMP NULL,
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id)  REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook-Event-Log (Idempotenz + Debugging)
CREATE TABLE IF NOT EXISTS billing_events (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stripe_event_id  VARCHAR(120) NOT NULL UNIQUE,
    event_type       VARCHAR(80) NOT NULL,
    org_id           INT UNSIGNED,
    payload          JSON,
    processed_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_org    (org_id),
    INDEX idx_type   (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Starter-Plan als Beispiel (inaktiv bis live)
INSERT IGNORE INTO subscription_plans (slug, name, description, price_cents, currency, interval_, active)
VALUES
    ('free',    'Kostenlos',  'Bis zu 10 Antworten/Monat, kein Kreditkarte', 0,    'EUR', 'month', 1),
    ('starter', 'Starter',   '100 Antworten/Monat, E-Mail-Support',          999,  'EUR', 'month', 0),
    ('pro',     'Pro',        'Unbegrenzt, Priority-Support',                 2499, 'EUR', 'month', 0);
