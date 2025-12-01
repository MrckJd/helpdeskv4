<?php

namespace App\Filament\Filters;

use Exception;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AssigneeFilter extends SelectFilter
{
    public static function make(?string $name = 'assignees-filter'): static
    {
        $filterClass = static::class;

        $name ??= static::getDefaultName();

        if (blank($name)) {
            throw new Exception("Filter of class [$filterClass] must have a unique name, passed to the [make()] method.");
        }

        $static = app($filterClass, ['name' => $name]);
        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Assignees');

        $this->placeholder('Select assignees');

        $this->relationship(
            'assignees',
            'name',
            function (Builder $query) {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                $query->when(
                    ! $user->root,
                    function (Builder $query) use ($user) {
                        $query->where('organization_id', $user->organization_id);
                    },
                    function (Builder $query) {
                        $query->where('organization_id', '!=', null);
                    }
                );

                $query->verifiedEmail()->approvedAccount()->withoutDeactivated()->withoutTrashed();
            }
        );

        $this->preload();

        $this->multiple();
    }
}
