<?php

namespace App\Filament\Clusters\Dossiers\Resources;

use Filament\Schemas\Schema;
use App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource\Pages\ListClosedDossiers;
use App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource\Pages\ViewDossier;
use App\Enums\ActionStatus;
use App\Filament\Clusters\Dossiers;
use App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource\Pages;
use App\Models\Dossier;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ClosedDossierResource extends Resource
{
    protected static ?string $model = Dossier::class;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-close-o';

    protected static ?string $cluster = Dossiers::class;

    protected static ?string $label = 'Closed';

    protected static ?string $slug = 'closed';

    protected static ?string $breadcrumb = 'Closed';

    protected static ?string $navigationLabel = 'Closed';

    public static function form(Schema $schema): Schema
    {
        return AllDossierResource::form($schema);
    }

    public static function table(Table $table): Table
    {
        return AllDossierResource::table($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AllDossierResource::infolist($schema);
    }

    public static function getRelations(): array
    {
        return AllDossierResource::getRelations();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClosedDossiers::route('/'),
            'show' => ViewDossier::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $panel = Filament::getCurrentOrDefaultPanel()->getId();

        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where(function ($query) {
                $query->whereDoesntHave('requests', function (Builder $query) {
                    $query->whereRelation('action', 'status', '!=', ActionStatus::CLOSED);
                });
            });

        return match ($panel) {
            'root' => $query,
            default => $query->where('organization_id', Auth::user()->organization_id),
        };
    }
}
