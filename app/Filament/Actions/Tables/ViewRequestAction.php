<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\ViewRequest;
use Filament\Tables\Actions\ViewAction;

class ViewRequestAction extends ViewAction
{
    use ViewRequest;

    protected function setUp(): void
    {
        $this->bootViewRequest();
    }
}
