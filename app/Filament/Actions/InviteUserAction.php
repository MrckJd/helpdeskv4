<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\InviteUser;
use Filament\Actions\Action;

class InviteUserAction extends Action
{
    use InviteUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootInviteUser();
    }
}
