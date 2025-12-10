<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    use HasUlids;

    protected $fillable = [
        'code',
        'name',
        'oldName',
        'isCapital',
        'isCity',
        'isMunicipality',
        'districtCode',
        'provinceCode',
        'regionCode',
        'islandGroupCode',
    ];

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class);
    }
}
