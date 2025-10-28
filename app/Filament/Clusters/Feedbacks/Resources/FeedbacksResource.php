<?php

namespace App\Filament\Clusters\Feedbacks\Resources;

use App\Filament\Clusters\Feedbacks;
use App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource\Pages;
use App\Jobs\GenerateFeedbackForm;
use App\Models\Feedback as FeedbackModel;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Unit;
use Spatie\LaravelPdf\Facades\Pdf;

class FeedbacksResource extends Resource
{
    protected static ?string $model = FeedbackModel::class;

    protected static ?string $navigationIcon = 'gmdi-feedback-r';

    protected static ?string $cluster = Feedbacks::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feedbacks.category_id')
                    ->label('Service Type')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->category?->name),
                TextColumn::make('organization.code')
                    ->label('Organization')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sqdAverage')
                    ->label('SQD Average')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : 'N/A')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault:true)
                    ->sortable(),
                ])
            ->filters([

            ])
            ->recordUrl(fn ($record): string => static::getUrl('view', ['record' => $record]))
            ->bulkActions([
                BulkAction::make('generated-pdf')
                    ->label('Generate')
                    ->icon('gmdi-picture-as-pdf')
                    ->action(function($records){
                        return GenerateFeedbackForm::dispatch($records);
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedbacks::route('/'),
            'view' => Pages\FeedbackForm::route('/{record}'),
        ];
    }
}
