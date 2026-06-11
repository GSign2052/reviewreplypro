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
}
