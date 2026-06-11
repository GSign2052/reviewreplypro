<?php

class ReviewReplyService
{
    public function generate(string $reviewText, string $industry, int $stars, string $tone): array
    {
        $riskLevel = $this->calcRiskLevel($stars);
        $variants  = [
            $this->buildReply($reviewText, $industry, $stars, $tone, 'A'),
            $this->buildReply($reviewText, $industry, $stars, $tone, 'B'),
            $this->buildReply($reviewText, $industry, $stars, $tone, 'C'),
        ];

        return [
            'reply_1'    => $variants[0],
            'reply_2'    => $variants[1],
            'reply_3'    => $variants[2],
            'risk_level' => $riskLevel,
        ];
    }

    private function calcRiskLevel(int $stars): string
    {
        if ($stars <= 2) return 'high';
        if ($stars === 3) return 'medium';
        return 'low';
    }

    private function buildReply(string $reviewText, string $industry, int $stars, string $tone, string $variant): string
    {
        $opening  = $this->opening($tone, $variant);
        $core     = $this->core($stars, $industry, $tone, $variant);
        $closing  = $this->closing($stars, $tone, $variant);

        return trim("$opening $core $closing");
    }

    private function opening(string $tone, string $variant): string
    {
        $map = [
            'freundlich'     => ['A' => 'Vielen herzlichen Dank für Ihre Bewertung!',
                                 'B' => 'Danke, dass Sie sich die Zeit genommen haben, uns zu bewerten!',
                                 'C' => 'Wir freuen uns sehr über Ihr Feedback!'],
            'professionell'  => ['A' => 'Vielen Dank für Ihre Bewertung.',
                                 'B' => 'Wir danken Ihnen für Ihr Feedback.',
                                 'C' => 'Herzlichen Dank für Ihre Rückmeldung.'],
            'entschuldigend' => ['A' => 'Vielen Dank, dass Sie sich die Zeit genommen haben, uns Ihr Feedback mitzuteilen.',
                                 'B' => 'Wir danken Ihnen für Ihre offene Rückmeldung.',
                                 'C' => 'Danke, dass Sie uns auf diesen Punkt aufmerksam gemacht haben.'],
            'premium'        => ['A' => 'Wir danken Ihnen aufrichtig für Ihre wertvolle Bewertung.',
                                 'B' => 'Ihr Feedback ist uns eine Herzensangelegenheit – vielen Dank.',
                                 'C' => 'Wir schätzen Ihre Rückmeldung außerordentlich und danken Ihnen herzlich.'],
        ];

        return $map[$tone][$variant] ?? 'Vielen Dank für Ihre Bewertung.';
    }

    private function core(int $stars, string $industry, string $tone, string $variant): string
    {
        if ($stars <= 2) {
            $cores = [
                'A' => "Es tut uns aufrichtig leid, dass Ihr Besuch in unserem $industry nicht Ihren Erwartungen entsprochen hat. Wir nehmen Ihre Kritik sehr ernst.",
                'B' => "Wir bedauern sehr, dass Sie keine positive Erfahrung in unserem $industry machen konnten. Ihr Feedback hilft uns, besser zu werden.",
                'C' => "Bitte entschuldigen Sie die Unannehmlichkeiten, die Sie in unserem $industry erlebt haben. Solche Erfahrungen entsprechen nicht unserem Anspruch.",
            ];
        } elseif ($stars === 3) {
            $cores = [
                'A' => "Wir freuen uns, dass Sie uns besucht haben, und nehmen Ihr Feedback als Antrieb, unser $industry-Angebot weiter zu verbessern.",
                'B' => "Ihr gemischtes Feedback zeigt uns, wo wir in unserem $industry noch Verbesserungspotenzial haben.",
                'C' => "Wir sind stets bemüht, das Erlebnis in unserem $industry zu optimieren, und Ihre Hinweise helfen uns dabei.",
            ];
        } else {
            $cores = [
                'A' => "Es freut uns sehr zu hören, dass Sie einen schönen Aufenthalt in unserem $industry hatten. Solche Rückmeldungen motivieren unser gesamtes Team.",
                'B' => "Wir sind begeistert, dass Ihr Besuch in unserem $industry so positiv war. Unser Team gibt täglich sein Bestes.",
                'C' => "Ihre positive Erfahrung in unserem $industry macht uns sehr stolz und bestärkt uns in unserem Engagement.",
            ];
        }

        return $cores[$variant] ?? $cores['A'];
    }

    private function closing(int $stars, string $tone, string $variant): string
    {
        if ($stars <= 2) {
            $closings = [
                'A' => 'Wir würden uns sehr freuen, wenn Sie uns die Möglichkeit geben würden, Ihre Erfahrung zu verbessern. Bitte kontaktieren Sie uns direkt – wir finden gemeinsam eine Lösung.',
                'B' => 'Bitte zögern Sie nicht, uns direkt zu kontaktieren, damit wir die Situation besprechen und für Sie lösen können.',
                'C' => 'Wir möchten sehr gerne mit Ihnen sprechen, um zu verstehen, wie wir Ihre Erfahrung hätten besser gestalten können.',
            ];
        } elseif ($stars === 3) {
            $closings = [
                'A' => 'Wir hoffen, Sie bald wieder bei uns begrüßen zu dürfen und Ihnen dann ein noch rundum positives Erlebnis bieten zu können.',
                'B' => 'Wir würden uns freuen, Sie erneut als Gast zu begrüßen und zu zeigen, was wir verbessert haben.',
                'C' => 'Ihr nächster Besuch soll Sie vollends überzeugen – wir freuen uns darauf, Sie wieder willkommen zu heißen.',
            ];
        } else {
            $closings = [
                'A' => 'Wir freuen uns darauf, Sie schon bald wieder bei uns begrüßen zu dürfen!',
                'B' => 'Auf ein baldiges Wiedersehen – Sie sind jederzeit herzlich willkommen!',
                'C' => 'Bis zum nächsten Mal – wir freuen uns immer über Ihren Besuch!',
            ];
        }

        return $closings[$variant] ?? $closings['A'];
    }
}
