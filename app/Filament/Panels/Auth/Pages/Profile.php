<?php

namespace App\Filament\Panels\Auth\Pages;

use App\Filament\Actions\ChangePasswordAction;
use App\Filament\Concerns\FormatsName;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\EditProfile;
use Filament\Support\Enums\Alignment;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use LSNepomuceno\LaravelA1PdfSign\Exceptions\ProcessRunTimeException;
use LSNepomuceno\LaravelA1PdfSign\Sign\ManageCert;
use SensitiveParameter;

class Profile extends EditProfile
{
    use FormatsName;

    protected $listeners = [
        'refresh' => '$refresh',
    ];

    public static function isSimple(): bool
    {
        return false;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ChangePasswordAction::make(),
        ];
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Section::make('Information')
                            ->description('Update your profile information.')
                            ->aside()
                            ->schema([
                                // FileUpload::make('avatar')
                                //     ->avatar()
                                //     ->alignCenter()
                                //     ->directory('avatars')
                                //     ->helperText('Upload a new profile picture.')
                                //     ->extraAttributes(['x-cloak']),
                                $this->getEmailFormComponent()
                                    ->prefixIcon('heroicon-o-at-symbol')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->helperText('You cannot change your email address.'),
                                $this->getNameFormComponent()
                                    ->prefixIcon('heroicon-o-tag')
                                    ->helperText('Enter your full legal name with proper capitalization.')
                                    ->dehydrateStateUsing(fn ($state) => $this->formatName($state)),
                                TextInput::make('designation')
                                    ->prefixIcon('heroicon-o-briefcase')
                                    ->helperText('Enter your current designation.')
                                    ->rule('required')
                                    ->markAsRequired()
                                    ->dehydrateStateUsing(fn ($state) => mb_ucfirst(mb_strtolower($state))),
                                TextInput::make('number')
                                    ->label('Mobile number')
                                    ->placeholder('9xx xxx xxxx')
                                    ->mask('999 999 9999')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->autofocus()
                                    ->helperText('(Optional) Enter your mobile number in the format 9xx-xxx-xxx')
                                    ->rule(fn () => function ($a, $v, $f) {
                                        if (! preg_match('/^9.*/', $v)) {
                                            $f('The mobile number field must follow a format of 9xx-xxx-xxxx.');
                                        }
                                    }),
                            ]),
                        Section::make('Signature')
                            ->relationship('signature', fn (?array $state) => filled($state['specimen']))
                            ->description('Update your signature specimen and certificate.')
                            ->footerActionsAlignment(Alignment::Right)
                            ->footerActions([
                                Action::make('delete')
                                    ->color('danger')
                                    ->requiresConfirmation()
                                    ->modalHeading(fn () => $this->getUser()->signature ? 'Delete signature' : 'No signature found')
                                    ->modalDescription($this->getUser()->signature ? 'Are you sure you want to delete your signature?' : '')
                                    ->modalSubmitAction(fn () => $this->getUser()->signature ? null : false)
                                    ->action(function () {
                                        $this->getUser()->signature->delete();

                                        $this->form->fill([
                                            ...$this->form->getRawState(),
                                            'signature' => [
                                                'specimen' => [],
                                                'certificate' => [],
                                                'password' => null,
                                            ],
                                        ]);

                                        $this->dispatch('refresh');
                                    }),
                            ])
                            ->aside()
                            ->schema([
                                FileUpload::make('specimen')
                                    ->required(fn ($get) => count($get('certificate')))
                                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/x-webp'])
                                    ->disk('local')
                                    ->directory('signatures/specimens')
                                    ->visibility('private')
                                    ->previewable(false)
                                    ->downloadable()
                                    ->helperText('Your signature specimen to be affixed in a signature field when signing a document.')
                                    ->hintIcon('heroicon-o-question-mark-circle')
                                    ->hintIconTooltip('The specimen should be a PNG or WebP image with a transparent background.')
                                    ->acceptedFileTypes(['image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->markAsRequired(false),
                                FileUpload::make('certificate')
                                    ->acceptedFileTypes(['application/x-pkcs12'])
                                    ->disk('local')
                                    ->directory('signatures/certificates')
                                    ->visibility('private')
                                    ->previewable(false)
                                    ->downloadable()
                                    ->helperText('Your certificate to be used to cryptographically sign a document to prove its authenticity.')
                                    ->hintIcon('heroicon-o-question-mark-circle')
                                    ->hintIconTooltip('The certificate should be a valid PKCS#12 file.')
                                    ->reactive(),
                                TextInput::make('password')
                                    ->password()
                                    ->visible(fn ($get) => current($get('certificate') ?? []) instanceof TemporaryUploadedFile)
                                    ->rule(fn ($get) => current($get('certificate') ?? []) instanceof TemporaryUploadedFile ? 'required' : null)
                                    ->dehydratedWhenHidden()
                                    ->helperText('Your certificate\'s password.')
                                    ->hintIcon('heroicon-o-question-mark-circle')
                                    ->hintIconTooltip('Your certificate\'s password is used to decrypt the certificate and sign documents. This password will be encrypted and stored securely.')
                                    ->rule(fn ($get) => function ($attribute, #[SensitiveParameter] $value, $fail) use ($get) {
                                        if (empty($value) || empty($get('certificate'))) {
                                            return;
                                        }

                                        if (! current($get('certificate')) instanceof TemporaryUploadedFile) {
                                            return;
                                        }

                                        try {
                                            (new ManageCert)->setPreservePfx()->fromUpload(current($get('certificate')), $value);
                                        } catch (ProcessRunTimeException $exception) {
                                            if (str($exception->getMessage())->contains('password')) {
                                                $fail('The password is incorrect.');
                                            }
                                        }
                                    }),
                            ]),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(),
            ),
        ];
    }
}
