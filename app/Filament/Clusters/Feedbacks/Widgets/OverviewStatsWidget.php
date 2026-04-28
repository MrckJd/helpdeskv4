<?php

namespace App\Filament\Clusters\Feedbacks\Widgets;

use App\Enums\UserRole;
use App\Models\Feedback;
use App\Models\Response;
use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Attributes\Reactive;

class OverviewStatsWidget extends StatsOverviewWidget
{
    #[Reactive]
    public ?string $selectedOrganizationId = null;

    protected static string $view = 'filament.panels.feedback.widgets.stats-overview-widget';

    protected function getStats(): array
    {
        $panelID = Filament::getCurrentPanel()->getId();

        return [
            Stat::make('CC Awareness', $this->getAwareness($panelID))
                ->description('Total number of feedback received.')
                ->color('primary'),
            Stat::make('CC Visibilty', $this->getVisibility($panelID))
                ->description('Number of positive feedback received.')
                ->color('success'),
            Stat::make('CC Helpfulness', $this->getHelpfulness($panelID))
                ->description('Number of negative feedback received.')
                ->color('danger'),
            Stat::make('Response Rate', $this->getResponseRate($panelID))
                ->description('Number of neutral feedback received.')
                ->color('warning'),
            Stat::make('Overall Score', 4.5)
                ->description('Average rating from feedback.')
                ->color('zinc')
                ->extraAttributes(['class' => 'col-span-1 max-sm:col-span-2']),
        ];
    }

    protected function getAwareness(string $panelID): string
    {   try{
            $partCount = $this->responsePartCountScope($this->selectedOrganizationId, $panelID)
                              ->where('question', 'CC1')
                              ->whereBetween('answer', [1,3])->count();
            $totalCount = $this->responseTotalCountScope($this->selectedOrganizationId, $panelID)
                               ->where('question', 'CC1')
                               ->count();

            return  Number::percentage(($partCount / $totalCount) * 100, 1);
        }catch (\DivisionByZeroError $e){
            return '0.0%';
        }

    }

    protected function getVisibility(string $panelID): string
    {
        try{
            $partCount = $this->responsePartCountScope($this->selectedOrganizationId, $panelID)
                          ->where('question', 'CC2')
                          ->where('answer', 1)->count();
            $totalCount = $this->responseTotalCountScope($this->selectedOrganizationId, $panelID)
                               ->where('question', 'CC2')
                               ->count();

            return  Number::percentage(($partCount / $totalCount) * 100, 1);
        }catch (\DivisionByZeroError $e){
            return '0.0%';
        }
    }

    protected function getHelpfulness(string $panelID) : string
    {
        try{
            $partCount = $this->responsePartCountScope($this->selectedOrganizationId, $panelID)
                          ->where('question', 'CC3')
                          ->where('answer', 1)->count();
            $totalCount = $this->responseTotalCountScope($this->selectedOrganizationId, $panelID)
                               ->where('question', 'CC3')
                               ->count();

            return  Number::percentage(($partCount / $totalCount) * 100, 1);
        }catch (\DivisionByZeroError $e){
            return '0.0%';
        }

    }

    protected function getResponseRate(string $panelID) : string
    {
        $totalTransactions = Transaction::sum('total_transactions');
        $totalResponses = Feedback::count();

        return  Number::percentage(($totalResponses / $totalTransactions) * 100, 1);
    }

    private function responsePartCountScope(?string $selectedOrganizationId, string $panelID): Builder
    {       $partCountQuery = Response::query();

            if ($panelID === UserRole::ADMIN->value){
                $partCountQuery->whereHas('feedback', function ($query){
                    $query->where('organization_id', request()->user()->organization_id);
                });
            }

            if($selectedOrganizationId) {
                $partCountQuery->whereHas('feedback', function ($query) use ($selectedOrganizationId) {
                    $query->where('organization_id', $selectedOrganizationId);
                });
            }

            return $partCountQuery;
    }

    private function responseTotalCountScope(?string $selectedOrganizationId, string $panelID): Builder
    {
        $totalCountQuery = Response::query();

        if ($panelID === UserRole::ADMIN->value){
            $totalCountQuery->whereHas('feedback', function ($query){
                $query->where('organization_id', request()->user()->organization_id);
            });
        }

        if($selectedOrganizationId) {
            $totalCountQuery->whereHas('feedback', function ($query) use ($selectedOrganizationId) {
                $query->where('organization_id', $selectedOrganizationId);
            });
        }

        return $totalCountQuery;
    }



}
