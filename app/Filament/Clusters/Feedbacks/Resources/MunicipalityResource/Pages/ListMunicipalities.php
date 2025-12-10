<?php

namespace App\Filament\Clusters\Feedbacks\Resources\MunicipalityResource\Pages;

use App\Filament\Clusters\Feedbacks\Resources\MunicipalityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
                    \App\Jobs\PSGCSync::dispatch();
                }),
        ];
    }
}
