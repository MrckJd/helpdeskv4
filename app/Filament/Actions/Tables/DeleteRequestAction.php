<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\DeleteAction;
use App\Filament\Actions\Concerns\DeleteRequest;

class DeleteRequestAction extends DeleteAction
{
    use DeleteRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootDeleteRequest();
    }
}
