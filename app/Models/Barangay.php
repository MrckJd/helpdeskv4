<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barangay extends Model
{
    use HasUlids;

    protected $fillable = [
        'code',
        'name',
        'oldName',
        'subMunicipalityCode',
        'cityCode',
        'municipalityCode',
        'districtCode',
        'provinceCode',
        'regionCode',
        'islandGroupCode',
        'municipality_id',
    ];

    public function municipality():BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
}
