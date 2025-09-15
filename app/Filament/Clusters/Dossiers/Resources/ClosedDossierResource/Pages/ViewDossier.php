<?php

namespace App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource\Pages;

use Filament\Actions\RestoreAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Actions\NoteDossierAction;
use App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDossier extends ViewRecord
{
    protected static string $resource = ClosedDossierResource::class;

    public function getHeading(): string
    {
        return str($this->record->name)->limit(36, '...', true);
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(array_slice(parent::getBreadcrumbs(), 0, -1), [
            str($this->record->name)->limit(36, '...', true),
        ]);
    }

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make()
                ->label('Restore')
                ->modalHeading('Restore dossier'),
            NoteDossierAction::make()
                ->icon(null),
            EditAction::make()
                ->label('Edit')
                ->hidden($this->record->trashed())
                ->slideOver(),
            ActionGroup::make([
                DeleteAction::make()
                    ->modalHeading('Delete dossier'),
                ForceDeleteAction::make()
                    ->modalHeading('Force delete dossier'),
            ]),
        ];
    }
}
