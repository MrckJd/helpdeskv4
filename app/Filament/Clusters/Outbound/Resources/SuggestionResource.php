<?php

namespace App\Filament\Clusters\Outbound\Resources;

use App\Enums\RequestClass;
use App\Filament\Actions\Tables\ViewRequestAction;
use App\Filament\Clusters\Outbound;
use App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\ListSuggestions;
use App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\NewSuggestion;
use App\Filament\Resources\RequestResource;

class SuggestionResource extends RequestResource
{
    public static ?bool $inbound = false;

    protected static ?string $cluster = Outbound::class;

    public static ?RequestClass $class = RequestClass::SUGGESTION;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $label = 'Suggestions';

    public static function getPages(): array
    {
        return [
            'index' => ListSuggestions::route('/'),
            'new' => NewSuggestion::route('new/{record}'),
        ];
    }

    public static function tableActions(): array
    {
        return [
            ViewRequestAction::make(),
        ];
    }
}
