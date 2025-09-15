<?php

namespace App\Filament\Clusters\Management\Resources\CategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Clusters\Management\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->slideOver()
                ->modalWidth(Width::Large),
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
