<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\RestoreAction;
use App\Filament\Actions\Concerns\RestoreRequest;

class RestoreRequestAction extends RestoreAction
{
    use RestoreRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRestoreRequest();
    }
}
