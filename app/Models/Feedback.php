<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feedback extends Model
{
    use HasUlids;
    protected $fillable = [
        'feedbacks',
        'category_id',
        'request_id',
        'organization_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'feedbacks' => 'array'
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }
}
