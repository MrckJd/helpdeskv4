<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\BulkAction;
use App\Filament\Actions\Concerns\ApproveAccount;

class ApproveAccountBulkAction extends BulkAction
{
    use ApproveAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootApproveUser();
    }
}
