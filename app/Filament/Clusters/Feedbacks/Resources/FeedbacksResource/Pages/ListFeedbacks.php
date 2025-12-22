<?php

namespace App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource\Pages;

use App\Enums\UserRole;
use App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFeedbacks extends ListRecords
{
    protected static string $resource = FeedbacksResource::class;

    public function getHeaderActions(): array
    {
        return [
            Action::make('transaction')
                ->label('Transaction')
                ->icon('uni-exchange-alt-o')
                ->hidden(fn () => !in_array(Filament::getCurrentPanel()->getId(), [UserRole::ROOT->value, UserRole::ADMIN->value]))
                ->sendSuccessNotification()
                ->form([
                    Repeater::make('transactions')
                        ->label('List of Transactions')
                        ->schema([
                            Select::make('category_id')
                                ->label('Service Type')
                                ->relationship('category', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('organization_id', Filament::auth()->user()->organization_id))
                                ->required(),
                            TextInput::make('total_transactions')
                                ->label('Total Transactions')
                                ->mask('9999999999')
                                ->required(),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->addActionLabel('Add Transaction')
                        ->reorderable(false),
                ])


        ];
    }

    public function getTabs(): array
    {
        $panel = Filament::getCurrentPanel()->getId();

        if($panel === UserRole::ROOT->value){
            return [
                'all' => Tab::make('All')
                    ->icon('gmdi-feedback-o')
                    ->badge(fn () => static::$resource::getEloquentQuery()->count()),
                'trashed' => Tab::make('Trashed')
                    ->modifyQueryUsing(fn ($query) => $query->onlyTrashed())
                    ->icon('gmdi-delete-o')
                    ->badgeColor('danger')
                    ->badge(fn () => static::$resource::getEloquentQuery()->onlyTrashed()->count()),
            ];
        }
        return [];
    }
}
