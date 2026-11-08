<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

/**
 * Mã giảm giá demo (FR-16). Thời hạn tính theo ngày chạy seed để demo lúc nào cũng dùng được.
 * HETHAN và DON500K dùng cho TC-30 (mã hết hạn / đơn chưa đủ tối thiểu).
 */
class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            // Giảm 10%, tối đa 100.000đ, 100 lượt
            ['code' => 'GIAM10', 'type' => Coupon::TYPE_PERCENT, 'value' => 10, 'min_order' => 0, 'max_discount' => 100000, 'usage_limit' => 100],
            // Giảm thẳng 50.000đ cho đơn từ 300.000đ
            ['code' => 'GIAM50K', 'type' => Coupon::TYPE_FIXED, 'value' => 50000, 'min_order' => 300000, 'max_discount' => null, 'usage_limit' => null],
            // Giảm 100.000đ cho đơn từ 500.000đ
            ['code' => 'DON500K', 'type' => Coupon::TYPE_FIXED, 'value' => 100000, 'min_order' => 500000, 'max_discount' => null, 'usage_limit' => null],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon + [
                'starts_at' => now()->subDays(7)->startOfDay(),
                'expires_at' => now()->addDays(60)->endOfDay(),
                'is_active' => true,
            ]);
        }

        // Đã hết hạn từ hôm qua
        Coupon::create([
            'code' => 'HETHAN',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 20,
            'min_order' => 0,
            'max_discount' => null,
            'usage_limit' => null,
            'starts_at' => now()->subDays(30)->startOfDay(),
            'expires_at' => now()->subDay()->endOfDay(),
            'is_active' => true,
        ]);
    }
}
