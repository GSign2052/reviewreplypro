<?php

require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/UserRepository.php';

class AuthService
{
    private UserRepository $users;

    public function __construct(PDO $db)
    {
        $this->users = new UserRepository($db);
    }

    /**
     * Neues Konto + Organisation anlegen, direkt einloggen.
     * Gibt [] bei Erfolg zurück, oder ['email' => '...'] mit Fehlern.
     */
    public function register(string $email, string $password, string $orgName): array
    {
        $errors = [];

        if ($this->users->emailExists($email)) {
            $errors['email'] = 'Diese E-Mail-Adresse ist bereits registriert.';
        }

        if ($errors) {
            return $errors;
        }

        $hash   = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $userId = $this->users->createWithOrg($orgName, $email, $hash);
        $user   = $this->users->findById($userId);

        SessionManager::setUser($userId, (int)$user['org_id'], $email);

        return [];
    }

    /**
     * Einloggen. Gibt true bei Erfolg zurück.
     */
    public function login(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        SessionManager::setUser((int)$user['id'], (int)$user['org_id'], $user['email']);
        return true;
    }

    public function logout(): void
    {
        SessionManager::destroy();
    }
}
