<?php

namespace App\Filament\Clusters\Management\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\RestoreAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Clusters\Management;
use App\Filament\Clusters\Management\Resources\TagResource\Pages\ListTags;
use App\Filament\Filters\OrganizationFilter;
use App\Models\Tag;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-sell-o';

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
                    ->placeholder('Select organization')
                    ->visible(fn (string $operation) => $panel === 'root' && $operation === 'create'),
                TextInput::make('name')
                    ->maxLength(24)
                    ->columnSpanFull()
                    ->live(debounce: 250)
                    ->rules('required')
                    ->markAsRequired()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($get, $rule) => $rule->where('organization_id', $get('organization_id'))),
                Select::make('color')
                    ->columnSpanFull()
                    ->options(array_reverse(array_combine(array_keys(Color::all()), array_map('ucfirst', array_keys(Color::all())))))
                    ->default('gray')
                    ->live(debounce: 250)
                    ->searchable()
                    ->required(),
                Placeholder::make('preview')
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'w-fit'])
                    ->content(fn ($get) => new HtmlString(Blade::render(
                        '<x-filament::badge color="'.($get('color') ?? 'gray').'">'.($get('name') ?: '&lt;empty&gt;').'</x-filament::badge>'
                    ))),
            ]);
    }

    public static function table(Table $table): Table
    {
        $panel = Filament::getCurrentOrDefaultPanel()->getId();

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tag')
                    ->color(fn (Tag $tag) => $tag->color ?? 'gray')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('organization.code')
                    ->visible($panel === 'root')
                    ->searchable(),
                TextColumn::make('requests_count')
                    ->label('Requests')
                    ->counts('requests'),
                TextColumn::make('open_count')
                    ->label('Open')
                    ->counts('open'),
                TextColumn::make('closed_count')
                    ->label('Closed')
                    ->counts('closed'),
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
                    ->withUnaffiliated(false)
                    ->visible($panel === 'root'),
            ])
            ->recordActions([
                RestoreAction::make(),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Large),
                ActionGroup::make([
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        match (Filament::getCurrentOrDefaultPanel()->getId()) {
            'root' => $query,
            'admin' => $query->where('organization_id', Auth::user()->organization_id),
            default => $query->whereRaw('1 = 0'),
        };

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->withoutTrashed()
            ->count();
    }
}
