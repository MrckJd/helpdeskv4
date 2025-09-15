<?php

namespace App\Filament\AvatarProviders;

use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Color\Rgb;

class UiAvatarsProvider extends \Filament\AvatarProviders\UiAvatarsProvider
{
    public function get(Model|Authenticatable $record): string
    {
        $name = str(Filament::getNameForDefaultAvatar($record))
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        $primaryColor = FilamentColor::getColors()['primary'][500];
            if (str_starts_with($primaryColor, '#')) {
                $backgroundColor = ltrim($primaryColor, '#');
            } else {
                try {
                    $backgroundColor = ltrim(Rgb::fromString($primaryColor)->toHex(), '#');
                } catch (\Throwable $e) {
                    // fallback to a default color if conversion fails
                    $backgroundColor = '000000';
                }
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=FFFFFF&background='.str($backgroundColor)->after('#');
    }
}
