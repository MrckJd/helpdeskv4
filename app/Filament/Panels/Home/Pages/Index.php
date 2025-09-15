<?php

namespace App\Filament\Panels\Home\Pages;

use Filament\Panel;
use Filament\Pages\Page;

class Index extends Page
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.panels.home.pages.index';

    protected static ?string $title = 'Home';

    protected ?string $heading = '';

    public static function getSlug(?Panel $panel = null): string
    {
        return '';
    }
}
