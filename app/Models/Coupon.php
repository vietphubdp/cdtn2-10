<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'type', 'value', 'min_order', 'max_discount', 'usage_limit', 'used_count',
    'starts_at', 'expires_at', 'is_active',
])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'min_order' => 'integer',
            'max_discount' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
