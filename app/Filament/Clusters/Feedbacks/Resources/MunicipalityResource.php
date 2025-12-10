<?php

namespace App\Filament\Clusters\Feedbacks\Resources;

use App\Filament\Clusters\Feedbacks;
use App\Filament\Clusters\Feedbacks\Resources\MunicipalityResource\Pages;
use App\Filament\Clusters\Feedbacks\Resources\MunicipalityResource\RelationManagers;
use App\Models\Municipality;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MunicipalityResource extends Resource
{
    protected static ?string $model = Municipality::class;

    protected static ?string $navigationIcon = 'gmdi-pin-drop-s';

    protected static ?string $cluster = Feedbacks::class;

    protected static ?string $label = 'Address';

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentPanel()->getID(),['root']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('barangays.name')
                    ->label('Barangays')
                    ->counts('barangays')
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->listWithLineBreaks()
                    ->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMunicipalities::route('/'),
        ];
    }
}
