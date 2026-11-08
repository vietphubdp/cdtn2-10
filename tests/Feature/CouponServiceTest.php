<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CouponService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponService $coupons;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coupons = new CouponService;
    }

    private function assertRejected(string $code, int $subtotal, string $message): void
    {
        try {
            $this->coupons->findUsable($code, $subtotal);
            $this->fail('Mã "'.$code.'" lẽ ra phải bị từ chối.');
        } catch (BusinessException $e) {
            $this->assertSame($message, $e->getMessage());
        }
    }

    private function shipping(): array
    {
        return [
            'receiver_name' => 'Nguyễn Văn A',
            'receiver_phone' => '0901234567',
            'shipping_address' => '12 Đường A, TP.HCM',
            'note' => null,
        ];
    }

    public function test_valid_code_is_found_case_insensitively(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'GIAM10']);

        $this->assertTrue($coupon->is($this->coupons->findUsable(' giam10 ', 100000)));
    }

    public function test_unknown_code_is_rejected(): void
    {
        $this->assertRejected('KHONGCO', 100000, 'Mã giảm giá "KHONGCO" không tồn tại.');
    }

    public function test_inactive_code_is_rejected(): void
    {
        Coupon::factory()->inactive()->create(['code' => 'TAT']);

        $this->assertRejected('TAT', 100000, 'Mã giảm giá "TAT" đã ngừng áp dụng.');
    }

    public function test_expired_code_is_rejected(): void
    {
        // TC-30
        Coupon::factory()->expired()->create(['code' => 'HETHAN']);

        $this->assertRejected('HETHAN', 100000, 'Mã giảm giá "HETHAN" đã hết hạn.');
    }

    public function test_code_not_started_is_rejected(): void
    {
        $coupon = Coupon::factory()->notStarted()->create(['code' => 'SAPTOI']);

        $this->assertRejected('SAPTOI', 100000,
            'Mã giảm giá "SAPTOI" chưa đến thời gian áp dụng (từ '.$coupon->starts_at->format('d/m/Y H:i').').');
    }

    public function test_used_up_code_is_rejected(): void
    {
        Coupon::factory()->usedUp()->create(['code' => 'HETLUOT']);

        $this->assertRejected('HETLUOT', 100000, 'Mã giảm giá "HETLUOT" đã hết lượt sử dụng.');
    }

    public function test_order_below_min_order_is_rejected(): void
    {
        // TC-30: đơn 100.000 với mã tối thiểu 500.000
        Coupon::factory()->fixed(100000)->create(['code' => 'DON500K', 'min_order' => 500000]);

        $this->assertRejected('DON500K', 100000, 'Mã "DON500K" chỉ áp dụng cho đơn từ 500.000đ, đơn hiện tại 100.000đ.');
        $this->assertSame('DON500K', $this->coupons->findUsable('DON500K', 500000)->code);
    }

    public function test_place_order_with_coupon_saves_discount_and_counts_usage(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'GIAM10', 'value' => 10, 'max_discount' => 100000, 'usage_limit' => 3, 'used_count' => 1]);
        $product = Product::factory()->create(['price' => 450000, 'stock' => 5]);

        $order = (new OrderService)->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 2], 'giam10');

        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(900000, $order->subtotal);
        $this->assertSame(90000, $order->discount);
        $this->assertSame(810000, $order->total);
        $this->assertSame(2, $coupon->fresh()->used_count);
    }

    public function test_place_order_without_coupon_still_works(): void
    {
        $product = Product::factory()->create(['price' => 200000, 'stock' => 5]);

        $order = (new OrderService)->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 1]);

        $this->assertNull($order->coupon_id);
        $this->assertSame(0, $order->discount);
        $this->assertSame(200000, $order->total);
    }

    public function test_coupon_used_up_before_ordering_rolls_back_everything(): void
    {
        // Khách khác vừa dùng lượt cuối sau khi khách này áp mã
        $coupon = Coupon::factory()->create(['code' => 'CUOI', 'usage_limit' => 1, 'used_count' => 1]);
        $product = Product::factory()->create(['stock' => 5]);

        try {
            (new OrderService)->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 1], 'CUOI');
            $this->fail('Đơn lẽ ra không được tạo.');
        } catch (BusinessException $e) {
            $this->assertSame('Mã giảm giá "CUOI" đã hết lượt sử dụng.', $e->getMessage());
        }

        $this->assertSame(0, Order::count());
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }
}
