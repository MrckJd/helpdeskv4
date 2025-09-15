<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Facades\Filament;

class Requests extends Cluster
{
    protected static ?int $navigationSort = 3;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-lifebuoy';

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentOrDefaultPanel()->getId(), ['root', 'admin', 'moderator', 'agent']);
    }
}
