<?php

class BillingRepository
{
    public function __construct(private PDO $db) {}

    public function getPlans(): array
    {
        return $this->db
            ->query('SELECT * FROM subscription_plans WHERE active = 1 ORDER BY price_cents')
            ->fetchAll();
    }

    public function getSubscriptionByOrg(int $orgId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, p.name AS plan_name, p.slug AS plan_slug, p.price_cents
             FROM subscriptions s
             JOIN subscription_plans p ON p.id = s.plan_id
             WHERE s.org_id = ?
             ORDER BY s.created_at DESC LIMIT 1'
        );
        $stmt->execute([$orgId]);
        return $stmt->fetch();
    }

    public function getStripeCustomer(int $orgId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM stripe_customers WHERE org_id = ?'
        );
        $stmt->execute([$orgId]);
        return $stmt->fetch();
    }

    public function upsertStripeCustomer(int $orgId, string $stripeCustomerId, string $email): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO stripe_customers (org_id, stripe_customer_id, email)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE stripe_customer_id = VALUES(stripe_customer_id), email = VALUES(email), updated_at = NOW()'
        );
        $stmt->execute([$orgId, $stripeCustomerId, $email]);
    }

    public function upsertSubscription(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO subscriptions
                 (org_id, plan_id, stripe_subscription_id, status, current_period_start, current_period_end, cancel_at_period_end, trial_ends_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                 status = VALUES(status),
                 current_period_start = VALUES(current_period_start),
                 current_period_end = VALUES(current_period_end),
                 cancel_at_period_end = VALUES(cancel_at_period_end),
                 trial_ends_at = VALUES(trial_ends_at),
                 updated_at = NOW()'
        );
        $stmt->execute([
            $data['org_id'],
            $data['plan_id'],
            $data['stripe_subscription_id'] ?? null,
            $data['status'],
            $data['current_period_start'] ?? null,
            $data['current_period_end'] ?? null,
            $data['cancel_at_period_end'] ?? 0,
            $data['trial_ends_at'] ?? null,
        ]);
    }

    public function logBillingEvent(string $stripeEventId, string $eventType, ?int $orgId, array $payload): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO billing_events (stripe_event_id, event_type, org_id, payload)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$stripeEventId, $eventType, $orgId, json_encode($payload)]);
    }

    public function eventAlreadyProcessed(string $stripeEventId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM billing_events WHERE stripe_event_id = ?');
        $stmt->execute([$stripeEventId]);
        return (bool) $stmt->fetch();
    }

    public function getPlanByStripePrice(string $stripePriceId): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM subscription_plans WHERE stripe_price_id = ?');
        $stmt->execute([$stripePriceId]);
        return $stmt->fetch();
    }
}
