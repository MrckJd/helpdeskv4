<?php

namespace App\Filament\Clusters\Management\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\RestoreAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Clusters\Management\Resources\OrganizationResource\Pages\ListOrganizations;
use App\Filament\Clusters\Management\Resources\OrganizationResource\Pages\CreateOrganization;
use App\Filament\Clusters\Management\Resources\OrganizationResource\Pages\EditOrganization;
use App\Filament\Clusters\Management;
use App\Filament\Clusters\Management\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrganizationResource extends Resource
{
    protected static ?int $navigationSort = -2;

    protected static ?string $model = Organization::class;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-domain-o';

    protected static ?string $cluster = Management::class;

    protected static ?string $recordTitleAttribute = 'code';

    public static function canAccess(): bool
    {
        return Filament::getCurrentOrDefaultPanel()->getId() === 'root';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                FileUpload::make('logo')
                    ->avatar()
                    ->alignCenter()
                    ->directory('logos'),
                Group::make()
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->autofocus()
                            ->unique(ignoreRecord: true)
                            ->markAsRequired()
                            ->rule('required'),
                        TextInput::make('code')
                            ->unique(ignoreRecord: true)
                            ->markAsRequired()
                            ->rule('required'),
                    ]),
                TextInput::make('address')
                    ->maxLength(255)
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 3,
                    ]),
                TextInput::make('building')
                    ->maxLength(255)
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                    ]),
                TextInput::make('room')
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_url')
                    ->label('')
                    ->circular()
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->grow(0),
                TextColumn::make('name')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->sortable(),
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
                EditAction::make(),
                ActionGroup::make([
                    DeleteAction::make()
                        ->modalDescription('Deleting this office will affect all related records associated with it e.g. categories and subcategories under this office.'),
                    ForceDeleteAction::make()
                        ->modalDescription(function () {
                            $description = <<<'HTML'
                                <p class="mt-2 text-sm text-gray-500 fi-modal-description dark:text-gray-400">
                                    Deleting this office will affect all related records associated with it e.g. categories and subcategories under this office.
                                </p>

                                <p class="mt-2 text-sm fi-modal-description text-custom-600 dark:text-custom-400" style="--c-400:var(--danger-400);--c-600:var(--danger-600);">
                                    Proceeding will permanently delete the office and all related records associated with it.
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
            'index' => ListOrganizations::route('/'),
            'create' => CreateOrganization::route('/create'),
            'edit' => EditOrganization::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->withoutTrashed()->count();
    }
}
