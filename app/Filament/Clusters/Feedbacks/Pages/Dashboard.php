<?php

namespace App\Filament\Clusters\Feedbacks\Pages;

use App\Filament\Actions\Header\SelectAction;
use App\Filament\Clusters\Feedbacks;
use App\Filament\Clusters\Feedbacks\Widgets\CustomerTypeWidget;
use App\Filament\Clusters\Feedbacks\Widgets\GenderChart;
use App\Filament\Clusters\Feedbacks\Widgets\TransactionOverview;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class Dashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.panels.feedback.pages.dashboard';

    protected static ?string $cluster = Feedbacks::class;

    protected static ?string $navigationIcon = 'gmdi-dashboard-o';

    #[Url(history: true)]
    public ?string $selectedOrganizationId = null;


    public function mount(): void
    {
        $this->form->fill();
    }

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
            SelectAction::make(),
            Action::make('report')
                ->label('Generate Report')
                ->icon('gmdi-file-download'),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            TransactionOverview::make([
                'selectedOrganizationId' => $this->selectedOrganizationId,
            ]),
            CustomerTypeWidget::make(),

            GenderChart::make([
                'selectedOrganizationId' => $this->selectedOrganizationId,
            ]),

            ];
    }


}
