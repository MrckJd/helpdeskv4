<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\Action;
use App\Filament\Actions\Concerns\RecallRequest;

class RecallRequestAction extends Action
{
    use RecallRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRecallRequest();
    }
}
