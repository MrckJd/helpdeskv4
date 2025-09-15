<?php

namespace App\Filament\Clusters\Dossiers\Resources\AllDossierResource\RelationManagers;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use App\Enums\ActionStatus;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Models\Request;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';

    protected static bool $isLazy = false;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('action.status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Request $request) => $request->action->status === ActionStatus::CLOSED ? $request->action->resolution : $request->action->status),
                TextColumn::make('code')
                    ->extraCellAttributes(['class' => 'font-mono'])
                    ->getStateUsing(fn (Request $request) => "#{$request->code}")
                    ->searchable(),
                TextColumn::make('subject')
                    ->searchable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Add request')
                    ->attachAnother(false)
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['code', 'subject'])
                    ->mutateFormDataUsing(fn (array $data) => [...$data, 'user_id' => Auth::id()]),
            ])
            ->recordActions([
                ShowRequestAction::make(),
                ViewRequestHistoryAction::make(),
                ActionGroup::make([
                    DetachAction::make()
                        ->label('Remove')
                        ->modalHeading('Remove request from dossier')
                        ->modalDescription('Are you sure you want to remove this request from this dossier?'),
                ]),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove selected requests from dossier')
                    ->modalDescription('Are you sure you want to remove these selected requests from this dossier?'),
            ])
            ->defaultSort('requests.created_at', 'desc');
    }
}
