<?php

namespace App\Filament\Actions\Header;

use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Actions\Concerns\HasSelect;

class SelectAction extends Action
{
    use HasSelect;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('select-organization');

        $this->view('filament.panels.feedback.components.SelectAction');

        $this->placeholder('Select Organization');



        $this->options(function($arguments){
            $selectedValue = $arguments['value'] ?? null;
            $options = Organization::pluck('code', 'id')->prepend('Select All Organizations', 'all')->toArray() ;

            if($selectedValue) {
                unset($options[$selectedValue]);
            }

            return $options;
        });

        $this->action(function ($arguments, $livewire) {
            $livewire->selectedOrganizationId = $arguments['value'] === 'all' ? null : $arguments['value'];
        });
    }

}
