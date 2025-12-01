<?php

namespace App\Filament\Concerns;

trait FormatsName
{
    public static function nameFormatter(?string $value)
    {
        return (new static)->formatName($value);
    }

    public function formatName(?string $value)
    {
        if (empty(trim($value))) {
            return '';
        }

        $mapping = [
            'jr' => 'Jr',
            'sr' => 'Sr',
            'ii' => 'II',
            'iii' => 'III',
            'iv' => 'IV',
            'v' => 'V',
            'vi' => 'VI',
            'vii' => 'VII',
            'viii' => 'VIII',
            'ix' => 'IX',
            'x' => 'X',
            'xi' => 'XI',
        ];

        $lowercase = [
            'da',
            'de',
            'del',
            'di',
            'la',
            'las',
            'los',
            'o',
            'van',
            'von',
            'y',
        ];

        $sanitize = function (?string $word) use ($lowercase, $mapping) {
            return collect(str($word)->squish()->trim()->lower()->split('/\s+/'))
                ->map(fn ($word) => @$mapping[str($word)->trim('.')->toString()] ?? $word)
                ->map(fn ($word) => in_array($word, $lowercase) ? $word : ucfirst($word))
                ->join(' ');
        };

        return $sanitize($value);
    }
}
