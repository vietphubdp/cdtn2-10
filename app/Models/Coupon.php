<?php

namespace App\Models;

use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'type', 'value', 'min_order', 'max_discount', 'usage_limit', 'used_count',
    'starts_at', 'expires_at', 'is_active',
])]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

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

    public function isPercent(): bool
    {
        return $this->type === self::TYPE_PERCENT;
    }

    public function isUsedUp(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    /**
     * Mô tả ngắn mức giảm: "10% (tối đa 100.000đ)" hoặc "50.000đ".
     */
    public function describe(): string
    {
        if (! $this->isPercent()) {
            return self::money($this->value);
        }

        return $this->value.'%'.($this->max_discount ? ' (tối đa '.self::money($this->max_discount).')' : '');
    }

    /**
     * Trạng thái hiện tại cho trang admin: [nhãn, màu badge Bootstrap].
     *
     * @return array{0: string, 1: string}
     */
    public function statusBadge(): array
    {
        return match (true) {
            ! $this->is_active => ['Đã tắt', 'secondary'],
            $this->starts_at->isFuture() => ['Chưa bắt đầu', 'info'],
            $this->expires_at->isPast() => ['Hết hạn', 'dark'],
            $this->isUsedUp() => ['Hết lượt', 'warning'],
            default => ['Đang áp dụng', 'success'],
        };
    }

    public static function money(int $amount): string
    {
        return number_format($amount, 0, ',', '.').'đ';
    }
}
