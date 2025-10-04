<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum Standardization: string implements HasLabel, HasDescription
{
    case ISO = 'iso';
    case ARTA = 'arta';

    public function getLabel(): string
    {
        return match ($this) {
            self::ISO => 'ISO',
            self::ARTA => 'ARTA',
            default => 'Unknown',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ISO => 'International Organization for Standardization',
            self::ARTA => 'Anti-Red Tape Act',
            default => null,
        };
    }
}
