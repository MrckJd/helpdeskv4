<?php

namespace App\Filament\Clusters\Feedbacks\Resources\MunicipalityResource\Pages;

use App\Filament\Clusters\Feedbacks\Resources\MunicipalityResource;
use App\Jobs\PSGCSync;
use App\Models\Barangay;
use App\Models\Municipality;
use App\Services\PSGCApiService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListMunicipalities extends ListRecords
{
    protected static string $resource = MunicipalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Sync Addresses')
                ->color('primary')
                ->icon('gmdi-sync-s')
                ->action(function () {
                    PSGCSync::dispatch();
                }),
        ];
    }
}
