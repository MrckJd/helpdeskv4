<?php

namespace App\Filament\Clusters\Management\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\RestoreAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Clusters\Management\Resources\UserResource\Pages\ListUsers;
use App\Enums\UserRole;
use App\Filament\Actions\Tables\ApproveAccountAction;
use App\Filament\Actions\Tables\DeactivateAccessAction;
use App\Filament\Clusters\Management;
use App\Filament\Clusters\Management\Resources\UserResource\Pages;
use App\Filament\Filters\OrganizationFilter;
use App\Filament\Filters\RoleFilter;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?int $navigationSort = -3;

    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-supervised-user-circle-o';

    protected static ?string $cluster = Management::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Account';

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentOrDefaultPanel()->getId(), ['root', 'admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FileUpload::make('avatar')
                    ->avatar()
                    ->alignCenter()
                    ->directory('avatars'),
                TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->markAsRequired()
                    ->rule('required')
                    ->prefixIcon('heroicon-o-user-circle'),
                TextInput::make('designation')
                    ->prefixIcon('heroicon-o-briefcase'),
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->visible(Filament::getCurrentOrDefaultPanel()->getId() === 'root')
                    ->prefixIcon('gmdi-business'),
                Select::make('role')
                    ->options(UserRole::options(Auth::user()->root))
                    ->prefixIcon('gmdi-shield-o')
                    ->default('user')
                    ->required(),
                TextInput::make('email')
                    ->rules(['email', 'required'])
                    ->unique(ignoreRecord: true)
                    ->markAsRequired()
                    ->prefixIcon('heroicon-o-at-symbol'),
                TextInput::make('number')
                    ->label('Number')
                    ->placeholder('9xx xxx xxxx')
                    ->mask('999 999 9999')
                    ->prefixIcon('heroicon-o-phone')
                    ->rule(fn () => function ($a, $v, $f) {
                        if (! preg_match('/^9.*/', $v)) {
                            $f('The mobile number field must follow a format of 9xx-xxx-xxxx.');
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        $panel = Filament::getCurrentOrDefaultPanel()->getId();

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->grow(false),
                TextColumn::make('name')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('organization.code')
                    ->visible($panel === 'root')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('role')
                    ->searchable(),
                TextColumn::make('approvedBy.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deactivatedBy.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deactivated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Affiliated organization')
                    ->visible($panel === 'root'),
                RoleFilter::make(),
            ])
            ->recordActions([
                ApproveAccountAction::make()
                    ->label('Approve'),
                RestoreAction::make(),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Medium),
                ActionGroup::make([
                    DeactivateAccessAction::make()
                        ->label(fn (User $user) => $user->deactivated_at ? 'Reactivate' : 'Deactivate'),
                    DeleteAction::make()
                        ->visible($panel === 'root'),
                    ForceDeleteAction::make()
                        ->visible($panel === 'root'),
                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->whereNot('id', Auth::id());

        return match (Filament::getCurrentOrDefaultPanel()->getId()) {
            'root' => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]),
            'admin' => $query->whereNot('role', UserRole::ROOT)
                ->whereNotNull('organization_id')
                ->where('organization_id', Auth::user()->organization_id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
