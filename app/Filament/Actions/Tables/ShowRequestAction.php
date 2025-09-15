<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\ViewAction;
use App\Filament\Actions\Concerns\ShowRequest;

class ShowRequestAction extends ViewAction
{
    use ShowRequest;

    protected function setUp(): void
    {
        $this->bootShowRequest();
    }
}
