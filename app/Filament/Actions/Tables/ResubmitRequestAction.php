<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\Action;
use App\Filament\Actions\Concerns\ResubmitRequest;

class ResubmitRequestAction extends Action
{
    use ResubmitRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootResubmitRequest();
    }
}
