<?php

class Validator
{
    private array $errors = [];

    public const INDUSTRIES = ['Restaurant', 'Friseur', 'Kosmetikstudio', 'Handwerker', 'Hotel', 'Fahrschule'];
    public const TONES      = ['freundlich', 'professionell', 'entschuldigend', 'premium'];

    public function validateReviewInput(array $data): bool
    {
        $this->errors = [];

        $text = trim($data['review_text'] ?? '');
        if ($text === '' || mb_strlen($text) < 10) {
            $this->errors[] = 'Bewertungstext muss mindestens 10 Zeichen lang sein.';
        } elseif (mb_strlen($text) > 2000) {
            $this->errors[] = 'Bewertungstext ist zu lang (max. 2000 Zeichen).';
        }

        $stars = isset($data['stars']) ? (int)$data['stars'] : 0;
        if ($stars < 1 || $stars > 5) {
            $this->errors[] = 'Sternezahl muss zwischen 1 und 5 liegen.';
        }

        if (!in_array($data['industry'] ?? '', self::INDUSTRIES, true)) {
            $this->errors[] = 'Ungültige Branche.';
        }

        if (!in_array($data['tone'] ?? '', self::TONES, true)) {
            $this->errors[] = 'Ungültiger Ton.';
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function validateRegisterInput(array $data): bool
    {
        $this->errors = [];

        $email = trim($data['email'] ?? '');
        if ($email === '') {
            $this->errors[] = 'E-Mail-Adresse ist erforderlich.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'E-Mail-Adresse ist ungültig.';
        } elseif (mb_strlen($email) > 255) {
            $this->errors[] = 'E-Mail-Adresse ist zu lang.';
        }

        $password = $data['password'] ?? '';
        if (mb_strlen($password) < 8) {
            $this->errors[] = 'Passwort muss mindestens 8 Zeichen lang sein.';
        }

        $confirm = $data['password_confirm'] ?? '';
        if ($password !== $confirm) {
            $this->errors[] = 'Passwörter stimmen nicht überein.';
        }

        $orgName = trim($data['org_name'] ?? '');
        if (mb_strlen($orgName) < 2) {
            $this->errors[] = 'Unternehmensname muss mindestens 2 Zeichen lang sein.';
        } elseif (mb_strlen($orgName) > 100) {
            $this->errors[] = 'Unternehmensname ist zu lang (max. 100 Zeichen).';
        }

        return empty($this->errors);
    }

    public function validateLoginInput(array $data): bool
    {
        $this->errors = [];

        $email = trim($data['email'] ?? '');
        if ($email === '') {
            $this->errors[] = 'E-Mail-Adresse ist erforderlich.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'E-Mail-Adresse ist ungültig.';
        }

        if (($data['password'] ?? '') === '') {
            $this->errors[] = 'Passwort ist erforderlich.';
        }

        return empty($this->errors);
    }
}
