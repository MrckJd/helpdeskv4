<?php

namespace App\Filament\Actions\Tables;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class EditTransactionsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'edit-transactions';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Edit Transactions');

        $this->icon('gmdi-edit-o');

        $this->color('warning');

        $this->hidden(fn () => !in_array(Filament::getCurrentPanel()->getId(), [UserRole::ROOT->value, UserRole::ADMIN->value]));

        // Pre-fill: default to current month and load its transactions
        $this->fillForm(function (Category $record): array {
            $currentMonth = now()->format('Y-m');

            $transactions = Transaction::query()
                ->where('organization_id', Filament::auth()->user()->organization_id)
                ->where('category_id', $record->id)
                ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
                ->orderBy('date')
                ->get(['id', 'date', 'total_transactions'])
                ->map(fn ($t) => [
                    'id'                 => $t->id,
                    'date'               => Carbon::parse($t->date)->format('F d, Y'),
                    'total_transactions' => $t->total_transactions,
                ])
                ->toArray();

            return [
                'filter_month' => $currentMonth,
                'transactions'  => $transactions,
            ];
        });

        $this->form([
            // Month filter — drives which entries appear in the repeater below
            Select::make('filter_month')
                ->label('Viewing Month')
                ->options(function (Category $record): array {
                    return Transaction::query()
                        ->where('organization_id', Filament::auth()->user()->organization_id)
                        ->where('category_id', $record->id)
                        ->whereNotNull('date')
                        ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month_key, DATE_FORMAT(date, '%M %Y') as month_label")
                        ->groupByRaw("DATE_FORMAT(date, '%Y-%m'), DATE_FORMAT(date, '%M %Y')")
                        ->orderByRaw("DATE_FORMAT(date, '%Y-%m') DESC")
                        ->pluck('month_label', 'month_key')
                        ->filter() // drop any remaining null labels
                        ->map(fn ($label) => (string) $label)
                        ->toArray();
                })
                ->placeholder('Select a month')
                ->live()
                ->afterStateUpdated(function (Set $set, ?string $state, Category $record): void {
                    $query = Transaction::query()
                        ->where('organization_id', Filament::auth()->user()->organization_id)
                        ->where('category_id', $record->id);

                    if ($state) {
                        [$year, $month] = explode('-', $state);
                        $query->whereYear('date', $year)->whereMonth('date', $month);
                    }

                    $transactions = $query->orderBy('date')
                        ->get(['id', 'date', 'total_transactions'])
                        ->map(fn ($t) => [
                            'id'                 => $t->id,
                            'date'               => Carbon::parse($t->date)->format('F d, Y'),
                            'total_transactions' => $t->total_transactions,
                        ])
                        ->toArray();

                    $set('transactions', $transactions);
                }),

            // Repeater — shows entries for the selected month
            Repeater::make('transactions')
                ->label('Transaction Entries')
                ->schema([
                    TextInput::make('id')
                        ->hidden()
                        ->dehydrated(),
                    TextInput::make('date')
                        ->label('Date')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('total_transactions')
                        ->label('Total Transactions')
                        ->numeric()
                        ->minValue(0)
                        ->mask('9999999999')
                        ->required(),
                ])
                ->columns(2)
                ->addable(false)
                ->deletable(false)
                ->reorderable(false),
        ]);

        $this->action(function (Category $record, array $data): void {
            try {
                $organizationId = Filament::auth()->user()->organization_id;

                foreach ($data['transactions'] as $entry) {
                    Transaction::query()
                        ->where('id', $entry['id'])
                        ->where('organization_id', $organizationId)
                        ->where('category_id', $record->id)
                        ->update([
                            'total_transactions' => $entry['total_transactions'],
                        ]);
                }

                Notification::make()
                    ->title('Transactions updated successfully.')
                    ->success()
                    ->send();

            } catch (\Exception $e) {
                Notification::make()
                    ->title('Failed to update transactions.')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        });

        $this->modalHeading(fn (Category $record) => "Edit Transactions — {$record->name}");

        $this->modalSubmitActionLabel('Save Changes');

        $this->modalWidth('lg');
    }
}
