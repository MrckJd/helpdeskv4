<?php

namespace App\Filament\Actions\Notifications;

use Filament\Actions\Action;
use App\Models\User;

class AcceptInvitationAction extends Action
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('accept-invitation');

        $this->markAsUnread();
    }

    public function for(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
