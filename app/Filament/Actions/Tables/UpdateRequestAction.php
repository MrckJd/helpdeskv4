<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\EditAction;
use App\Filament\Actions\Concerns\UpdateRequest;

class UpdateRequestAction extends EditAction
{
    use UpdateRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootUpdateRequest();
    }
}
