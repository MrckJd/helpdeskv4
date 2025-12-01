<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    use HasUlids;

    protected $fillable = [
        'specimen',
        'certificate',
        'password',
        'user_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public static function booted(): void
    {
        static::deleting(function (self $signature) {
            Storage::delete($signature->specimen);

            if ($signature->certificate) {
                Storage::delete($signature->certificate);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
