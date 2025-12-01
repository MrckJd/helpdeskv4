<?php

namespace App\Filament\Panels\Home\Pages;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Models\Request;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;

class Index extends Page
{
    public array $data = [];

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.panels.home.pages.index';

    protected static ?string $title = 'Home';

    protected ?string $heading = '';

    public static function getSlug(): string
    {
        return '';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label('Code')
                    ->hiddenLabel()
                    ->extraInputAttributes(['class' => 'font-mono'])
                    ->string()
                    ->rule('required')
                    ->markAsRequired()
                    ->exists('requests', 'code')
                    ->maxLength(11)
                    ->placeholder('#zxywvu9876')
                    ->mutateStateForValidationUsing(fn (?string $state) => preg_replace('/[^a-zA-Z0-9]/', '', $state))
                    ->dehydrateStateUsing(fn (?string $state) => preg_replace('/[^a-zA-Z0-9]/', '', $state))
                    ->validationMessages([
                        'exists' => 'Request not found.',
                        'required' => 'Please enter a request code',
                    ])
                    ->prefixAction(
                        FormAction::make('search')
                            ->icon('gmdi-search-o')
                            ->submit('search')
                            ->color('primary')
                            ->extraAttributes(['class' => 'font-mono'])
                    ),
            ])
            ->statePath('data');
    }

    public function search(): void
    {
        $this->replaceMountedAction('result', [
            $this->form->getState()['code'],
        ]);

        $this->form->fill(['code' => '']);
    }

    public function result(): Action
    {
        return Action::make('result')
            ->record(fn (array $arguments) => Request::firstWhere('code', reset($arguments)))
            ->modalIcon(fn (Request $request) => $request->class->getIcon())
            ->modalIconColor(fn (Request $request) => $request->class->getColor())
            ->modalHeading(fn (Request $request) => str("Show {$request->class->value} <span class='font-mono'>#{$request->code}</span>")->toHtmlString())
            ->modalDescription(fn (Request $request) => $request->user->name)
            ->modalSubmitAction(false)
            ->modalWidth(MaxWidth::ExtraLarge)
            ->modalCancelActionLabel('Close')
            ->slideOver()
            ->infolist(function (Request $request) {
                return [
                    Tabs::make('tabs')
                        ->contained(false)
                        ->activeTab(2)
                        ->tabs([
                            Tab::make('Request')
                                ->schema([
                                    TextEntry::make('tags')
                                        ->hiddenLabel()
                                        ->badge()
                                        ->alignEnd()
                                        ->hidden(fn (Request $request) => $request->tags->isEmpty())
                                        ->color(fn (string $state) => $request->tags->first(fn ($tag) => $tag->name === $state)?->color ?? 'gray')
                                        ->state(fn (Request $request) => $request->tags->pluck('name')->toArray()),
                                    TextEntry::make('from.name')
                                        ->hiddenLabel()
                                        ->helperText(fn (Request $request) => $request->user->name),
                                    TextEntry::make('action.status')
                                        ->hiddenLabel()
                                        ->size(TextEntry\TextEntrySize::ExtraSmall)
                                        ->state(fn (Request $request) => $request->action->status === ActionStatus::CLOSED ? $request->action->resolution : $request->action->status),
                                    TextEntry::make('subject')
                                        ->hiddenLabel()
                                        ->weight(FontWeight::Bold)
                                        ->size(TextEntry\TextEntrySize::Large),
                                    TextEntry::make('submitted.created_at')
                                        ->hiddenLabel()
                                        ->color('gray')
                                        ->dateTime('F j, Y \a\t H:i'),
                                    ViewEntry::make('body')
                                        ->label('Inquiry')
                                        ->hiddenLabel(false)
                                        ->view('filament.requests.show', [
                                            'request' => $request,
                                        ]),
                                    ViewEntry::make('responses')
                                        ->visible($request->class === RequestClass::INQUIRY)
                                        ->view('filament.requests.history', [
                                            'request' => $request,
                                            'chat' => true,
                                            'descending' => false,
                                        ]),
                                ]),
                            Tab::make('History')
                                ->schema([
                                    ViewEntry::make('history')
                                        ->view('filament.requests.history', [
                                            'request' => $request,
                                        ]),
                                ]),
                        ]),
                ];
            });
    }
}
