<?php

namespace App\Filament\Clusters\Dossiers\Resources;

use Filament\Schemas\Schema;
use App\Filament\Clusters\Dossiers\Resources\OpenDossierResource\Pages\ListOpenDossiers;
use App\Filament\Clusters\Dossiers\Resources\OpenDossierResource\Pages\ViewDossier;
use App\Enums\ActionStatus;
use App\Filament\Clusters\Dossiers;
use App\Filament\Clusters\Dossiers\Resources\OpenDossierResource\Pages;
use App\Models\Dossier;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class OpenDossierResource extends Resource
{
    protected static ?int $navigationSort = -2;

    protected static ?string $model = Dossier::class;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-circle-o';

    protected static ?string $cluster = Dossiers::class;

    protected static ?string $label = 'Open';

    protected static ?string $slug = 'open';

    protected static ?string $breadcrumb = 'Open';

    protected static ?string $navigationLabel = 'Open';

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
            'index' => ListOpenDossiers::route('/'),
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
                $query->whereHas('requests', function (Builder $query) {
                    $query->whereRelation('action', 'status', '!=', ActionStatus::CLOSED);
                });
            });

        return match ($panel) {
            'root' => $query,
            default => $query->where('organization_id', Auth::user()->organization_id),
        };
    }
}
