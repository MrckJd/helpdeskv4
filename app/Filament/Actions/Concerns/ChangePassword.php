<?php

namespace App\Filament\Actions\Concerns;

use App\Models\User;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

trait ChangePassword
{
    use CanCustomizeProcess;

    protected bool $authenticated = false;

    protected ?User $user = null;

    protected function bootChangePassword(): void
    {
        $this->process(fn (User $user) => $this->user = $user->exists ? $user : Auth::user());

        $this->name('change-password');

        $this->icon('gmdi-lock');

        $this->visible(fn () => $this->user->hasVerifiedEmail() && $this->user->hasApprovedAccount() && $this->user->hasActiveAccess() && ! $this->user->trashed());

        $this->modalIcon('gmdi-lock');

        $this->modalSubmitActionLabel('Change Password');

        $this->modalDescription($this->user->is(Auth::user()) ? 'You will be logged out after changing your password' : 'Change this account\'s password.');

        $this->modalFooterActionsAlignment(Alignment::Justify);

        $this->modalWidth('md');

        $this->successNotificationTitle('Password changed');

        $this->form([
            TextInput::make('password')
                ->password()
                ->currentPassword()
                ->rule('required')
                ->markAsRequired()
                ->helperText('Enter your current password.')
                ->visible($this->user->is(Auth::user())),
            TextInput::make('new_password')
                ->password()
                ->rule('required')
                ->markAsRequired()
                ->rule(Password::defaults())
                ->helperText('Enter new password.')
                ->same('passwordConfirmation'),
            TextInput::make('passwordConfirmation')
                ->password()
                ->rule('required')
                ->markAsRequired()
                ->helperText('Confirm new password.')
                ->dehydrated(false),
        ]);

        $this->action(function (array $data) {
            $this->user->forceFill(['password' => $data['new_password']])->save();

            $this->sendSuccessNotification();

            if ($this->user->is(Auth::user())) {
                Filament::auth()->logout();

                session()->invalidate();
                session()->regenerate();

                return redirect()->route('filament.auth.auth.login');
            }
        });
    }
}
