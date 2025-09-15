<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\Action;
use App\Filament\Actions\Concerns\DeactivateAccess;

class DeactivateAccessAction extends Action
{
    use DeactivateAccess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootDeactivateUser();
    }
}
