<?php

namespace App\Filament\Actions\Tables;

use Filament\Actions\Action;
use App\Filament\Actions\Concerns\NoteDossier;

class NoteDossierAction extends Action
{
    use NoteDossier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootTraitDossierAction();
    }
}
