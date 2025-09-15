<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Facades\Filament;

class Management extends Cluster
{
    protected static ?int $navigationSort = -1;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-layers-o';

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentOrDefaultPanel()->getId(), ['root', 'admin']);
    }
}
