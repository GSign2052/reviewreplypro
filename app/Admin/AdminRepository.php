<?php

class AdminRepository
{
    public function __construct(private PDO $db) {}

    public function getStats(): array
    {
        return [
            'total_orgs'   => (int) $this->db->query('SELECT COUNT(*) FROM organizations')->fetchColumn(),
            'total_users'  => (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'total_replies'=> (int) $this->db->query('SELECT COUNT(*) FROM review_replies')->fetchColumn(),
            'new_today'    => (int) $this->db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
            'new_week'     => (int) $this->db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
        ];
    }

    public function getCustomers(int $limit = 50): array
    {
        return $this->db->query(
            'SELECT o.id AS org_id, o.name AS org_name, o.created_at AS org_created,
                    u.email, u.created_at AS user_created,
                    COUNT(rr.id) AS reply_count
             FROM organizations o
             JOIN users u ON u.org_id = o.id
             LEFT JOIN review_replies rr ON rr.org_id = o.id
             GROUP BY o.id, u.id
             ORDER BY o.created_at DESC
             LIMIT ' . (int) $limit
        )->fetchAll();
    }

    public function getBillingStatus(): array
    {
        // Graceful: Tabelle existiert evtl. noch nicht in allen Umgebungen
        try {
            return $this->db->query(
                'SELECT o.name AS org_name, s.status, p.name AS plan_name, s.current_period_end
                 FROM subscriptions s
                 JOIN organizations o ON o.id = s.org_id
                 JOIN subscription_plans p ON p.id = s.plan_id
                 ORDER BY s.updated_at DESC LIMIT 50'
            )->fetchAll();
        } catch (PDOException) {
            return [];
        }
    }

    public function getRecentErrors(int $lines = 50): array
    {
        $logFile = ROOT . '/storage/logs/app.log';
        if (!file_exists($logFile)) return [];

        $all = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice(array_reverse($all), 0, $lines);
    }

    public function getLiveReadiness(): array
    {
        return [
            'stripe_keys_set'  => str_starts_with((string) env('STRIPE_SECRET_KEY', ''), 'sk_live_'),
            'billing_enabled'  => billingEnabled(),
            'legal_live'       => legalLive(),
            'https_env'        => !empty($_SERVER['HTTPS']) || env('APP_URL', '') !== '' && str_starts_with(env('APP_URL', ''), 'https'),
            'env_is_production'=> appEnv() === 'production',
        ];
    }
}
