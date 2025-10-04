<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionResolution;
use App\Enums\ActionStatus;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\HtmlString;

class ResolveRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::CLOSED;

    protected static ?ActionResolution $requestResolution = ActionResolution::RESOLVED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('resolve-request');

        $this->label('Close');

        // $this->slideOver();

        $this->icon(ActionResolution::RESOLVED->getIcon());

        // $this->modalIcon(ActionResolution::RESOLVED->getIcon());

        $this->modalHeading('Close request');

        $this->modalSubmitAction(false);

        $this->modalCancelAction(false);

        $this->modalDescription('Before closing this request permanently and mark it as resolved. You are highly encouraged to take the Customer Satisfaction Survey.');

        // $this->modalWidth(MaxWidth::TwoExtraLarge);

        $this->closeModalByClickingAway(false);

        $this->successNotificationTitle('Request closed');

        $this->failureNotificationTitle('Request closure failed');

        $this->form([
            // MarkdownEditor::make('remarks'),
            // FileAttachment::make(),

            Wizard::make([
                Step::make('Privacy Policy')
                    ->description('Please review our privacy policy before closing this request.')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->prefixIcon('heroicon-o-at-symbol')
                            ->placeholder('Enter your email address')
                            ->default(Auth::user()->email),
                        DateTimePicker::make('date')
                            ->label('Date')
                            ->prefixIcon('heroicon-o-calendar')
                            ->default(Date::now()),
                        Checkbox::make('consent')
                            ->extraAttributes(['class' => 'place-self-start mt-1'])
                            ->required()
                            ->accepted()
                            ->validationMessages([
                                'accepted' => 'You must accept the privacy policy to proceed.',
                            ])
                            ->label(function(){
                                $html = new HtmlString(
                                    '<div class="text-justify">
                                        In compliance with the  <b>Republic Act 10173 (RA 10173)</b> or also known as
                                        <i>Data Privacy Act of 2012</i>, we are committed to protecting your personal
                                        information and ensuring its confidentiality and security. We kindly seek
                                         your explicit consent to collect, process, and store your personal data
                                         for legitimate and authorized purposes related to our services.
                                         Please rest assured that your information will be handled in accordance
                                         with the principles of transparency, accountability, and lawful processing,
                                         with all necessary security measures in place to protect it from unauthorized
                                         access or misuse. By providing your consent, you acknowledge and agree to the terms
                                         outlined in our privacy policy.
                                    </div>
                                    '
                                );

                                return $html;
                            }),
                    ]),
                Step::make('Personal Information')
                    ->description('Please provide your personal Information')
                    ->columns(2)
                    ->schema([
                        Select::make('client_type')
                            ->label('Client Type')
                            ->required()
                            ->columnSpan(2)
                            ->placeholder('Select your client type')
                            ->options([
                                'citizen' => 'Citizen',
                                'business' => 'Business',
                                'government' => 'Government (Employee or Another Agency)',
                            ]),
                        Select::make('gender')
                            ->label('Gender')
                            ->placeholder('Select your Gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        TextInput::make('age')
                            ->label('Age')
                            ->placeholder('Enter your age')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(120),
                        TextInput::make('residence')
                            ->label('Region of Residence')
                            ->placeholder('Enter your place of residence')
                            ->maxLength(255)
                            ->columnSpan(2),
                    ]),
                Step::make('Remarks & Attachments'),
            ])
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->action->status !== ActionStatus::COMPLETED) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'status' => ActionStatus::CLOSED,
                    'resolution' => ActionResolution::RESOLVED,
                    'remarks' => $data['remarks'],
                    'user_id' => Auth::id(),
                ]);

                if (count($data['files']) > 0) {
                    $action->attachment()->create([
                        'files' => $data['files'],
                        'paths' => $data['paths'],
                    ]);
                }

                $this->commitDatabaseTransaction();

                $this->sendSuccessNotification();

                $this->notifyUsers();
            } catch (Exception) {
                $this->rollBackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->hidden(fn (Request $request) => $request->action->status->finalized() ?: $request->action->status !== ActionStatus::COMPLETED);
    }
}
