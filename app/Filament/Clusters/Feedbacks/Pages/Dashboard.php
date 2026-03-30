<?php

namespace App\Filament\Clusters\Feedbacks\Pages;

use App\Filament\Clusters\Feedbacks;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string $view = 'filament.panels.feedback.pages.dashboard';

    protected static ?string $cluster = Feedbacks::class;

    protected static ?string $navigationIcon = 'gmdi-dashboard-o';

    public function getBreadcrumbs(): array
    {
        return array_merge(
            parent::getBreadcrumbs(),
            ['Dashboard'],
        );
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('report')
                ->label('Generate Report')
                ->icon('gmdi-file-download')

        ];
    }
}
