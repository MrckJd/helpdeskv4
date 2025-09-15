<?php

namespace App\Filament\Clusters\Management\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\RestoreAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Clusters\Management\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Clusters\Management\Resources\CategoryResource\Pages\ListSubcategories;
use App\Filament\Clusters\Management;
use App\Filament\Clusters\Management\Resources\CategoryResource\Pages;
use App\Filament\Filters\OrganizationFilter;
use App\Models\Category;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-folder-zip-o';

    protected static ?string $cluster = Management::class;

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentOrDefaultPanel()->getId(), ['root', 'admin']);
    }

    public static function form(Schema $schema): Schema
    {
        $panel = Filament::getCurrentOrDefaultPanel()->getId();

        return $schema
            ->components([
                Select::make('organization_id')
                    ->columnSpanFull()
                    ->relationship('organization', 'code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => $panel !== 'root' ? Auth::user()->organization_id : null)
                    ->visible(fn (string $operation) => $panel === 'root' && $operation === 'create')
                    ->dehydratedWhenHidden(),
                TextInput::make('name')
                    ->label('Name')
                    ->columnSpanFull()
                    ->dehydrateStateUsing(fn (?string $state) => mb_ucfirst($state ?? ''))
                    ->maxLength(48)
                    ->rule('required')
                    ->markAsRequired()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn ($rule, $get) => $rule->withoutTrashed()
                            ->where('organization_id', $get('organization'))
                    ),
                Repeater::make('subcategories')
                    ->relationship()
                    ->columnSpanFull()
                    ->addActionLabel('Add subcategory')
                    ->deletable(fn (string $operation) => $operation === 'create')
                    ->addable(fn (string $operation) => $operation === 'create')
                    ->simple(
                        TextInput::make('name')
                            ->distinct()
                            ->maxLength(48)
                            ->rule('required')
                            ->markAsRequired()
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        $panel = Filament::getCurrentOrDefaultPanel()->getId();

        return $table
            ->columns([
                TextColumn::make('name')
                    ->description(fn (Category $category) => $panel === 'root' ? $category->organization->code : null)
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('subcategories.name')
                    ->searchable(isIndividual: true)
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList(),
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
            ->filters([
                OrganizationFilter::make()
                    ->withUnaffiliated(false),
            ])
            ->recordActions([
                RestoreAction::make(),
                Action::make('subcategories')
                    ->icon('gmdi-folder-special-o')
                    ->url(fn (Category $category) => static::getUrl('subcategories', [$category->id])),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Large),
                ActionGroup::make([
                    DeleteAction::make()
                        ->modalDescription('Deleting this category will affect all related records associated with it e.g. subcategories under this category.'),
                    ForceDeleteAction::make()
                        ->modalDescription(function () {
                            $description = <<<'HTML'
                                <p class="mt-2 text-sm text-gray-500 fi-modal-description dark:text-gray-400">
                                    Deleting this category will affect all related records associated with it e.g. subcategories under this category.
                                </p>

                                <p class="mt-2 text-sm fi-modal-description text-custom-600 dark:text-custom-400" style="--c-400:var(--warning-400);--c-600:var(--warning-600);">
                                    Proceeding with this action will permanently delete the category and all related records associated with it.
                                </p>
                            HTML;

                            return str($description)->toHtmlString();
                        }),
                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'subcategories' => ListSubcategories::route('/{record}/subcategories'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        return match (Filament::getCurrentOrDefaultPanel()->getId()) {
            'root' => $query,
            'admin' => $query->where('organization_id', Auth::user()->organization_id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->withoutTrashed()
            ->count();
    }
}
