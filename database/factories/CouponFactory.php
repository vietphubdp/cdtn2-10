<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Mặc định: mã giảm 10%, không giới hạn, đang trong hạn.
 *
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'MA'.strtoupper(fake()->unique()->bothify('??##??')),
            'type' => Coupon::TYPE_PERCENT,
            'value' => 10,
            'min_order' => 0,
            'max_discount' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }

    public function fixed(int $value): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Coupon::TYPE_FIXED,
            'value' => $value,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
        ]);
    }

    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->addDay(),
            'expires_at' => now()->addMonth(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function usedUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_limit' => 5,
            'used_count' => 5,
        ]);
    }
}
