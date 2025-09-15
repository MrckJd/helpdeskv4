<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Facades\Filament;

class Outbound extends Cluster
{
    protected static ?int $navigationSort = 4;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-call-split-o';

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentOrDefaultPanel()->getId(), ['admin', 'moderator']);
    }
}
