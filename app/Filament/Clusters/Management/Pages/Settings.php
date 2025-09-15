<?php

namespace App\Filament\Clusters\Management\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use App\Filament\Clusters\Management;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Settings extends Page
{
    use InteractsWithFormActions;

    public array $data = [];

    protected static ?int $navigationSort = PHP_INT_MAX;

    protected static string | \BackedEnum | null $navigationIcon = 'gmdi-settings-o';

    protected string $view = 'filament.panels.admin.clusters.organization.pages.settings';

    protected static ?string $cluster = Management::class;

    public static function canAccess(): bool
    {
        return Filament::getCurrentOrDefaultPanel()->getId() !== 'root';
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->fillForm();
    }

    public function getBreadcrumbs(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs([
                Settings::getUrl() => static::getTitle(),
            ]);
        }

        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->contained(false)
                    ->columns(3)
                    ->tabs([
                        Tab::make('Organization')
                            ->icon('gmdi-domain-o')
                            ->schema([
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
                                            ->unique(ignoreRecord: true)
                                            ->markAsRequired()
                                            ->rule('required'),
                                        TextInput::make('code')
                                            ->markAsRequired()
                                            ->rule('required')
                                            ->unique(ignoreRecord: true),
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
                            ]),
                        Tab::make('Configuration')
                            ->icon('gmdi-build-circle-o')
                            ->schema([
                                TextInput::make('settings.auto_queue')
                                    ->label('Request auto queue')
                                    ->placeholder('Number of minutes')
                                    ->helperText('Number of minutes to auto queue a request')
                                    ->rules(['numeric']),
                                TextInput::make('settings.auto_resolve')
                                    ->label('Request auto resolve')
                                    ->placeholder('Number of hours')
                                    ->helperText('Number of hours to auto resolve a completed request')
                                    ->minValue(48)
                                    ->rules(['numeric']),
                                TextInput::make('settings.auto_assign')
                                    ->label('Request auto assign')
                                    ->placeholder('Number of minutes')
                                    ->helperText('Number of minutes to auto assign a request')
                                    ->rules(['numeric']),
                                // Forms\Components\Toggle::make('settings.support_reassignment')
                                //     ->inline(false)
                                //     ->disabled(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function update(): void
    {
        $data = $this->form->getState();

        $organization = Organization::find(Auth::user()->organization_id);

        DB::transaction(function () use ($data, $organization) {
            $organization->update($data);

            Notification::make()
                ->success()
                ->title('Settings updated')
                ->send();
        });
    }

    protected function fillForm(): void
    {
        $this->form->fill(Organization::find(Auth::user()->organization_id)->toArray());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Update')
                ->submit('update')
                ->keyBindings(['mod+s']),
        ];
    }
}
