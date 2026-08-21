<?php

namespace App\Filament\Clusters\Feedbacks\Widgets;

use App\Enums\UserRole;
use App\Filament\Clusters\Feedbacks\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Feedback;
use App\Models\Request;
use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Livewire\Attributes\Reactive;

class TransactionOverview extends BaseWidget
{
    use InteractsWithPageTable;

    #[Reactive]
    public ?string $selectedOrganizationId = null;

    protected function getTablePage(): string
    {
        return ListCategories::class;
    }

    protected function getStats(): array
    {
        $panelID = Filament::getCurrentPanel()->getId();

        $selectedOrganizationId = $this->tableFilters['organization_id']['value'] ?? null;
        $selectServiceType = $this->tableFilters['service_type']['value'] ?? null;

        return [
            Stat::make('Total Transaction', $this->getTotalTransactions($selectedOrganizationId, $panelID, $selectServiceType))
                ->description('Total number of transactions recorded.')
                ->color('primary')
                ->chart($this->totalTransactionsChart()),
            Stat::make('Total Surveyed', $this->getTotalSurveyed($selectedOrganizationId, $panelID, $selectServiceType))
                ->color('success')
                ->description('Total number of transactions that have been surveyed.')
                ->chart($this->totalSurveyedChart()),
            Stat::make('Total Not Surveyed', $this->getTotalNotSurveyed($selectedOrganizationId, $panelID, $selectServiceType))
                ->description('Total number of transactions that have not been surveyed.')
                ->color('danger'),
            Stat::make('Percentage', $this->getPercentage($this->getTotalSurveyed($selectedOrganizationId, $panelID, $selectServiceType),
                                $this->getTotalTransactions($selectedOrganizationId, $panelID, $selectServiceType)))
                ->description('Percentage of transactions that have been surveyed.')
                ->color('zinc'),
        ];
    }

    private function totalTransactionsChart() : array {
        return Request::where('organization_id', Auth::user()->organization_id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count')
            ->toArray();
    }

    private function totalSurveyedChart(): array {
        return Feedback::where('organization_id', Auth::user()->organization_id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count')
            ->toArray();
    }

    private function getTotalTransactions(?string $organizationId, string $panelID, ?string $serviceType): int
    {
        return Transaction::query()
            ->tap(function ($query) use ($panelID){
                match ($panelID){
                    UserRole::ADMIN->value => $query->where('organization_id', Auth::user()->organization_id),
                    default => $query,
                };
            })
            ->when($organizationId, function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->when($serviceType, function ($query) use ($serviceType) {
                $query->whereHas('category', function ($query) use ($serviceType) {
                    $query->where('service_type', $serviceType);
                });
            })
            ->sum('total_transactions');

    }

    private function getTotalSurveyed(?string $organizationId, string $panelID, ?string $serviceType): int
    {
        return Feedback::query()
            ->tap(function ($query) use ($panelID){
                match ($panelID){
                    UserRole::ADMIN->value => $query->where('organization_id', Auth::user()->organization_id),
                    default => $query,
                };
            })
            ->when($organizationId, function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->when($serviceType, function ($query) use ($serviceType) {
                $query->whereHas('category', function ($query) use ($serviceType) {
                    $query->where('service_type', $serviceType);
                });
            })
            ->count();
    }

    private function getTotalNotSurveyed(?string $organizationId, string $panelID, ?string $serviceType): int
    {


        if ($panelID ===  UserRole::ADMIN->value){

            $userOrganization = Auth::user()->organization_id;

            $totalTransactions= Transaction::where('organization_id', $userOrganization)->sum('total_transactions');

            $totalSurveyed = Feedback::where('organization_id', $userOrganization)->count();

            return $totalTransactions - $totalSurveyed;
        }

        if(!$organizationId){

            $totalSurveyed = Feedback::count();

            return Transaction::sum('total_transactions') - $totalSurveyed;
        }else{
            $totalTransactions = Transaction::where('organization_id',$organizationId)->sum('total_transactions');

            $totalSurveyed = Feedback::where('organization_id',$organizationId)->count();

            return $totalTransactions - $totalSurveyed;
        }

        return 0;
    }

    private function getPercentage(int $totalSurveyed, int $totalTransactions): string
    {
        if ($totalTransactions === 0) {
            return '0%';
        }
        $percentage = ($totalSurveyed / $totalTransactions) * 100;
        return number_format($percentage, 2) . '%';
    }
}
