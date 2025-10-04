<?php

namespace App\Filament\Clusters\Feedbacks\Resources;

use App\Filament\Clusters\Feedback;
use App\Filament\Clusters\Feedbacks;
use App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource\Pages;
use App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource\RelationManagers;
use App\Models\Feedback as FeedbackModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeedbacksResource extends Resource
{
    protected static ?string $model = FeedbackModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Feedbacks::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('feedbacks.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feedbacks.category_id')
                    ->label('Service Type')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->category?->name ?? 'N/A'),
                TextColumn::make('organization.code')
                    ->label('Organization')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feedbacks.date')
                    ->label('Date')
                    ->date()
                    ->searchable()
                    ->sortable(),
                ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedbacks::route('/'),
            'create' => Pages\CreateFeedbacks::route('/create'),
            'edit' => Pages\EditFeedbacks::route('/{record}/edit'),
        ];
    }
}
