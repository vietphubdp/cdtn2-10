<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Services\CouponService;
use PHPUnit\Framework\TestCase;

/**
 * Tính tiền giảm (BR-09) – hàm tính toán độc lập, không cần CSDL.
 */
class CouponDiscountTest extends TestCase
{
    private function coupon(array $attributes): Coupon
    {
        return new Coupon($attributes + ['max_discount' => null]);
    }

    public function test_percent_discount_is_rounded_down(): void
    {
        $coupon = $this->coupon(['type' => Coupon::TYPE_PERCENT, 'value' => 10]);

        $this->assertSame(79000, (new CouponService)->discountFor($coupon, 790000));
        $this->assertSame(3333, (new CouponService)->discountFor($coupon, 33339));
    }

    public function test_percent_discount_does_not_exceed_max_discount(): void
    {
        $coupon = $this->coupon(['type' => Coupon::TYPE_PERCENT, 'value' => 10, 'max_discount' => 100000]);

        $this->assertSame(100000, (new CouponService)->discountFor($coupon, 2500000));
        $this->assertSame(50000, (new CouponService)->discountFor($coupon, 500000));
    }

    public function test_fixed_discount(): void
    {
        $coupon = $this->coupon(['type' => Coupon::TYPE_FIXED, 'value' => 50000]);

        $this->assertSame(50000, (new CouponService)->discountFor($coupon, 300000));
    }

    public function test_discount_never_makes_total_negative(): void
    {
        $fixed = $this->coupon(['type' => Coupon::TYPE_FIXED, 'value' => 200000]);
        $full = $this->coupon(['type' => Coupon::TYPE_PERCENT, 'value' => 100]);

        $this->assertSame(150000, (new CouponService)->discountFor($fixed, 150000));
        $this->assertSame(150000, (new CouponService)->discountFor($full, 150000));
    }

    public function test_code_is_normalized_to_uppercase(): void
    {
        $this->assertSame('GIAM10', CouponService::normalize('  giam10 '));
    }
}
