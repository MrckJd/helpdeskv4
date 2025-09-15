<?php

namespace App\Filament\Clusters\Dossiers\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\TextSize;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Actions\Action;
use Filament\Infolists\Components\ViewEntry;
use App\Filament\Clusters\Dossiers\Resources\AllDossierResource\Pages\ListDossiers;
use App\Filament\Clusters\Dossiers\Resources\AllDossierResource\Pages\ViewDossier;
use App\Filament\Actions\Tables\NoteDossierAction;
use App\Filament\Clusters\Dossiers;
use App\Filament\Clusters\Dossiers\Resources\AllDossierResource\Pages;
use App\Filament\Clusters\Dossiers\Resources\AllDossierResource\RelationManagers\RequestsRelationManager;
use App\Models\Dossier;
use App\Models\Note;
use App\Models\Request;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AllDossierResource extends Resource
{
    protected static ?int $navigationSort = -2;

    protected static ?string $model = Dossier::class;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-list-o';

    protected static ?string $cluster = Dossiers::class;

    protected static ?string $label = 'All';

    protected static ?string $slug = 'all';

    protected static ?string $breadcrumb = 'All';

    protected static ?string $navigationLabel = 'All';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(Auth::id()),
                Select::make('organization_id')
                    ->columnSpanFull()
                    ->visible(Auth::user()->root)
                    ->default(Auth::user()->organization_id)
                    ->dehydratedWhenHidden()
                    ->relationship('organization', 'name')
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->columnSpanFull()
                    ->required(),
                MarkdownEditor::make('description')
                    ->columnSpanFull()
                    ->required(),
                Repeater::make('records')
                    ->visibleOn('create')
                    ->columnSpanFull()
                    ->relationship()
                    ->required()
                    ->addActionLabel('Add record')
                    ->defaultItems(1)
                    ->simple(
                        Select::make('request_id')
                            ->relationship(
                                'request',
                                'code',
                                fn (Builder $query) => $query->where(function ($query) {
                                    if (Auth::user()->root) {
                                        return;
                                    }

                                    $query->where('organization_id', Auth::user()->organization_id);

                                    $query->orWhere('from_id', Auth::user()->organization_id);
                                }),
                            )
                            ->searchable(['code', 'subject'])
                            ->distinct()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Request $request) => "#{$request->code} — {$request->subject}")
                            ->required()
                            ->validationMessages(['distinct' => 'These fields must not have a duplicate value.']),
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->limit(36)
                    ->wrap()
                    ->tooltip(fn ($column) => strlen($column->getState()) > $column->getCharacterLimit() ? $column->getState() : null),
                TextColumn::make('requests_count')
                    ->counts('requests')
                    ->label('Requests'),
                TextColumn::make('user.name')
                    ->searchable(['name', 'email']),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                NoteDossierAction::make(),
                ViewAction::make()
                    ->url(fn (Dossier $dossier, Component $livewire) => $livewire::getResource()::getUrl('show', ['record' => $dossier->id])),
            ])
            ->emptyStateHeading('No dossiers found')
            ->emptyStateDescription('Create a new dossier to make a collection of related requests.')
            ->recordAction(null);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextEntry::make('name')
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold),
                TextEntry::make('created')
                    ->weight(FontWeight::SemiBold)
                    ->state(fn (Dossier $record) => "By {$record->user->name} on {$record->created_at->format('jS \of F Y')} at {$record->created_at->format('H:i')}"
                    ),
                TextEntry::make('description')
                    ->visible(fn (Dossier $record) => filled($record->description))
                    ->markdown(),
                RepeatableEntry::make('notes')
                    // ->contained(false)
                    ->visible(fn (Dossier $record) => $record->notes->isNotEmpty())
                    ->schema([
                        TextEntry::make('user.name')
                            ->suffixAction(fn (Note $note) => Action::make('delete-'.$note->id)
                                ->requiresConfirmation()
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->modalHeading('Delete note')
                                ->visible(Auth::user()->is($note->user) || Auth::user()->admin)
                                ->action(function () use ($note) {
                                    $note->delete();

                                    Notification::make()
                                        ->title('Note deleted')
                                        ->success()
                                        ->send();
                                }),
                            )
                            ->getStateUsing(function (Note $note) {
                                $username = $note->user?->name ?? '<i>(non-existent user)</i>';

                                return str("<b>{$username}</b> on {$note->created_at->format('jS \of F Y')} at {$note->created_at->format('H:i')}")
                                    ->toHtmlString();
                            })
                            ->hiddenLabel(),
                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->markdown(),
                        ViewEntry::make('attachment')
                            ->hiddenLabel()
                            ->visible(fn (Note $note) => $note->attachment?->exists)
                            ->view('filament.attachments.show'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RequestsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDossiers::route('/'),
            'show' => ViewDossier::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $panel = Filament::getCurrentOrDefaultPanel()->getId();

        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        return match ($panel) {
            'root' => $query,
            default => $query->where('organization_id', Auth::user()->organization_id),
        };
    }
}
