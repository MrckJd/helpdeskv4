<?php

namespace App\Filament\Clusters\Management\Resources;

use App\Enums\Standardization;
use App\Filament\Clusters\Management;
use App\Filament\Clusters\Management\Resources\CategoryResource\Pages;
use App\Filament\Filters\OrganizationFilter;
use App\Models\Category;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'gmdi-folder-zip-o';

    protected static ?string $cluster = Management::class;

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentPanel()->getId(), ['root', 'admin']);
    }

    public static function form(Form $form): Form
    {
        $panel = Filament::getCurrentPanel()->getId();

        return $form
            ->schema([
                Forms\Components\Select::make('organization_id')
                    ->columnSpanFull()
                    ->relationship('organization', 'code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => $panel !== 'root' ? Auth::user()->organization_id : null)
                    ->visible(fn (string $operation) => $panel === 'root' && $operation === 'create')
                    ->dehydratedWhenHidden(),
                Forms\Components\TextInput::make('name')
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
                Forms\Components\Select::make('standard_type')
                    ->label('Standard Type')
                    ->columnSpanFull()
                    ->options(Standardization::class)
                    ->native(false),
                Forms\Components\Repeater::make('subcategories')
                    ->relationship()
                    ->columnSpanFull()
                    ->addActionLabel('Add subcategory')
                    ->deletable(fn (string $operation) => $operation === 'create')
                    ->addable(fn (string $operation) => $operation === 'create')
                    ->simple(
                        Forms\Components\TextInput::make('name')
                            ->distinct()
                            ->maxLength(48)
                            ->rule('required')
                            ->markAsRequired()
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        $panel = Filament::getCurrentPanel()->getId();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Category $category) => $panel === 'root' ? new HtmlString('<div class="flex flex-col text-sm text-gray-500 dark:text-gray-400"><p>'.$category->standard_type?->getLabel().'</p><p>('.$category->organization?->code.')</p></div>') : $category->standard_type?->getLabel())
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('subcategories.name')
                    ->searchable(isIndividual: true)
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList(),
                Tables\Columns\TextColumn::make('requests_count')
                    ->label('Requests')
                    ->counts('requests'),
                Tables\Columns\TextColumn::make('open_count')
                    ->label('Open')
                    ->counts('open'),
                Tables\Columns\TextColumn::make('closed_count')
                    ->label('Closed')
                    ->counts('closed'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                OrganizationFilter::make()
                    ->withUnaffiliated(false),
            ])
            ->actions([
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\Action::make('subcategories')
                    ->icon('gmdi-folder-special-o')
                    ->url(fn (Category $category) => static::getUrl('subcategories', [$category->id])),
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth(MaxWidth::Large),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->modalDescription('Deleting this category will affect all related records associated with it e.g. subcategories under this category.'),
                    Tables\Actions\ForceDeleteAction::make()
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
            'index' => Pages\ListCategories::route('/'),
            'subcategories' => Pages\ListSubcategories::route('/{record}/subcategories'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        return match (Filament::getCurrentPanel()->getId()) {
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
