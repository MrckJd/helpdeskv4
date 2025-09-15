<?php

namespace App\Filament\Clusters\Management\Resources\CategoryResource\Pages;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Support\Enums\Width;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\RestoreAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use App\Enums\RequestClass;
use App\Filament\Actions\Tables\TemplatesPreviewActionGroup;
use App\Filament\Clusters\Management\Resources\CategoryResource;
use App\Models\Subcategory;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListSubcategories extends ManageRelatedRecords
{
    protected static string $resource = CategoryResource::class;

    protected static string $relationship = 'subcategories';

    public function getTabs(): array
    {
        $query = fn () => $this->record->subcategories();

        return [
            'all' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed())
                ->icon('gmdi-verified-o')
                ->badge(fn () => $query()->withoutTrashed()->count()),
            'trashed' => Tab::make('Trashed')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->icon('gmdi-delete-o')
                ->badgeColor('danger')
                ->badge(fn () => $query()->onlyTrashed()->count()),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(array_slice(parent::getBreadcrumbs(), 0, -1), [
            $this->record->name,
            'Subcategories',
            'List',
        ]);
    }

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(static::$resource::getUrl()),
            CreateAction::make()
                ->model(Subcategory::class)
                ->createAnother(false)
                ->slideOver()
                ->modalWidth(Width::Large)
                ->closeModalByClickingAway(false)
                ->schema(fn (Schema $schema) => [
                    Select::make('category_id')
                        ->columnSpanFull()
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->default($this->record->getKey())
                        ->hidden()
                        ->dehydratedWhenHidden(),
                    ...$this->form($schema)->getComponents(),
                ]),
        ];
    }

    public function getHeading(): string
    {
        return "{$this->record->name} → Subcategories";
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->dehydrateStateUsing(fn (?string $state) => mb_ucfirst($state ?? ''))
                    ->rule('required')
                    ->markAsRequired()
                    ->maxLength(48),
                Group::make()
                    ->relationship('inquiryTemplate')
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => [...$data, 'class' => RequestClass::INQUIRY])
                    ->schema([
                        MarkdownEditor::make('content')
                            ->label('Inquiry Template')
                            ->nullable(),
                    ]),
                Group::make()
                    ->relationship('suggestionTemplate')
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => [...$data, 'class' => RequestClass::SUGGESTION])
                    ->schema([
                        MarkdownEditor::make('content')
                            ->label('Suggestion Template')
                            ->nullable(),
                    ]),
                Group::make()
                    ->relationship('ticketTemplate')
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => [...$data, 'class' => RequestClass::TICKET])
                    ->schema([
                        MarkdownEditor::make('content')
                            ->label('Ticket Template')
                            ->nullable(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        $panel = Filament::getCurrentOrDefaultPanel()->getId();

        return $table
            ->heading($panel === 'root' ? "{$this->record->organization->code} → {$this->record->name} → Subcategories" : null)
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requests_count')
                    ->label('Requests')
                    ->counts('requests'),
                TextColumn::make('open_count')
                    ->label('Open')
                    ->counts('open'),
                TextColumn::make('closed_count')
                    ->label('Closed')
                    ->counts('closed'),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                RestoreAction::make(),
                TemplatesPreviewActionGroup::make(),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Large)
                    ->closeModalByClickingAway(false),
                ActionGroup::make([
                    DeleteAction::make()
                        ->modalDescription('Deleting this subcategory will affect all related records associated with it.'),
                    ForceDeleteAction::make()
                        ->modalDescription(function () {
                            $description = <<<'HTML'
                                <p class="mt-2 text-sm text-gray-500 fi-modal-description dark:text-gray-400">
                                    Deleting this subcategory will affect all related records associated with it.
                                </p>

                                <p
                                    class="mt-2 text-sm fi-modal-description text-custom-600 dark:text-custom-400"
                                    style="--c-400:var(--warning-400);--c-600:var(--warning-600);"
                                >
                                    Proceeding with this action will permanently delete the subcategory and all related records associated with it.
                                </p>
                            HTML;

                            return str($description)->toHtmlString();
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->groups([
                Tables\Grouping\Group::make('category.name')
                    ->label('Organization')
                    ->getDescriptionFromRecordUsing(fn (Subcategory $subcategory) => "({$subcategory->category->organization->code}) {$subcategory->category->organization->name}")
                    ->titlePrefixedWithLabel(false),
            ])
            ->groupingSettingsHidden()
            ->recordAction(null)
            ->recordUrl(null);
    }
}
