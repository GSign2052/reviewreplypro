<?php

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, org_id, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, org_id, email, role FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    public function createWithOrg(string $orgName, string $email, string $passwordHash): int
    {
        $this->db->beginTransaction();

        try {
            // Organisation anlegen
            $stmt = $this->db->prepare('INSERT INTO organizations (name) VALUES (:name)');
            $stmt->execute([':name' => $orgName]);
            $orgId = (int)$this->db->lastInsertId();

            // Benutzer anlegen
            $stmt = $this->db->prepare(
                'INSERT INTO users (org_id, email, password_hash, role) VALUES (:org_id, :email, :hash, "owner")'
            );
            $stmt->execute([
                ':org_id' => $orgId,
                ':email'  => $email,
                ':hash'   => $passwordHash,
            ]);
            $userId = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $userId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getOrgId(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT org_id FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}
