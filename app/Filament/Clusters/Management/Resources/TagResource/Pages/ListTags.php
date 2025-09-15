<?php

namespace App\Filament\Clusters\Management\Resources\TagResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Clusters\Management\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->slideOver()
                ->modalWidth(Width::Large)
                ->mutateDataUsing(function (array $data) {
                    return [
                        ...$data,
                        'organization_id' => $data['organization_id'] ?? Auth::user()->organization_id,
                    ];
                }),
        ];
    }

    public function getTabs(): array
    {
        $query = fn () => static::$resource::getEloquentQuery();

        return [
            'all' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed())
                ->icon('gmdi-verified-o')
                ->badge(fn () => $query()->withoutTrashed()->count()),
            'trashed' => Tab::make('Trashed')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->icon('gmdi-delete-o')
                ->badgeColor('danger')
                ->badge(fn () => $query()->onlyTrashed()->count()),
        ];
    }
}
