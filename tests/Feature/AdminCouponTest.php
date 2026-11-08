<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'tet2027',
            'type' => 'percent',
            'value' => 15,
            'min_order' => 200000,
            'max_discount' => 150000,
            'usage_limit' => 50,
            'starts_at' => '2026-11-01T00:00',
            'expires_at' => '2026-12-31T23:59',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_admin_sees_coupon_list_and_sidebar_menu(): void
    {
        Coupon::factory()->create(['code' => 'GIAM10', 'value' => 10, 'max_discount' => 100000, 'usage_limit' => 100, 'used_count' => 3]);
        Coupon::factory()->expired()->create(['code' => 'HETHAN']);

        $this->actingAs($this->admin())
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee('href="'.route('admin.coupons.index').'"', false)
            ->assertSeeInOrder(['HETHAN', 'Hết hạn'])
            ->assertSeeInOrder(['GIAM10', '10% (tối đa 100.000đ)', '3 / 100', 'Đang áp dụng']);
    }

    public function test_admin_creates_coupon(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.coupons.store'), $this->validData())
            ->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHas('success', 'Đã thêm mã giảm giá "TET2027".');

        $coupon = Coupon::sole();
        $this->assertSame('TET2027', $coupon->code);
        $this->assertSame(15, $coupon->value);
        $this->assertSame(150000, $coupon->max_discount);
        $this->assertSame(0, $coupon->used_count);
        $this->assertTrue($coupon->is_active);
        $this->assertSame('2026-12-31 23:59', $coupon->expires_at->format('Y-m-d H:i'));
    }

    public function test_fixed_coupon_ignores_max_discount_and_defaults_min_order(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.coupons.store'), $this->validData([
                'type' => 'fixed', 'value' => 50000, 'max_discount' => 10000, 'min_order' => '', 'usage_limit' => '',
            ]))
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::sole();
        $this->assertNull($coupon->max_discount);
        $this->assertNull($coupon->usage_limit);
        $this->assertSame(0, $coupon->min_order);
    }

    public function test_invalid_coupon_data_is_rejected(): void
    {
        Coupon::factory()->create(['code' => 'GIAM10']);

        $this->actingAs($this->admin())
            ->from(route('admin.coupons.create'))
            ->post(route('admin.coupons.store'), $this->validData([
                'code' => 'giam10',
                'value' => 150,
                'expires_at' => '2026-10-01T00:00',
            ]))
            ->assertRedirect(route('admin.coupons.create'))
            ->assertSessionHasErrors(['code', 'value' => 'Giảm theo phần trăm chỉ được từ 1 đến 100.', 'expires_at']);

        $this->post(route('admin.coupons.store'), $this->validData(['code' => 'CÓ DẤU', 'type' => 'free']))
            ->assertSessionHasErrors(['code', 'type']);

        $this->assertSame(1, Coupon::count());
    }

    public function test_admin_updates_coupon_and_can_turn_it_off(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'GIAM10', 'used_count' => 4]);

        $this->actingAs($this->admin())
            ->get(route('admin.coupons.edit', $coupon))
            ->assertOk()
            ->assertSee('value="GIAM10"', false)
            ->assertSee('Đã dùng 4 lượt.');

        // Giữ nguyên mã của chính nó không bị báo trùng
        $this->put(route('admin.coupons.update', $coupon), $this->validData(['code' => 'GIAM10', 'value' => 20, 'is_active' => '0']))
            ->assertRedirect(route('admin.coupons.index'));

        $coupon->refresh();
        $this->assertSame(20, $coupon->value);
        $this->assertFalse($coupon->is_active);
        $this->assertSame(4, $coupon->used_count);
    }

    public function test_usage_limit_cannot_be_below_used_count(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'GIAM10', 'usage_limit' => 10, 'used_count' => 4]);

        $this->actingAs($this->admin())
            ->put(route('admin.coupons.update', $coupon), $this->validData(['code' => 'GIAM10', 'usage_limit' => 3]))
            ->assertSessionHasErrors(['usage_limit' => 'Số lượt sử dụng phải từ 4 trở lên (không nhỏ hơn số lượt đã dùng).']);

        $this->assertSame(10, $coupon->fresh()->usage_limit);
    }

    public function test_admin_deletes_unused_coupon(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'XOA']);

        $this->actingAs($this->admin())
            ->get(route('admin.coupons.index'))
            ->assertSee('action="'.route('admin.coupons.destroy', $coupon).'"', false);

        $this->delete(route('admin.coupons.destroy', $coupon))
            ->assertSessionHas('success', 'Đã xóa mã giảm giá "XOA".');

        $this->assertModelMissing($coupon);
    }

    public function test_used_coupon_cannot_be_deleted(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'DADUNG']);
        Order::factory()->create(['coupon_id' => $coupon->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.coupons.index'))
            ->assertSee('DADUNG')
            ->assertDontSee('action="'.route('admin.coupons.destroy', $coupon).'"', false);

        $this->delete(route('admin.coupons.destroy', $coupon))
            ->assertSessionHas('error', 'Không thể xóa mã "DADUNG": mã đã được dùng trong đơn hàng, chỉ có thể tắt.');

        $this->assertModelExists($coupon);
    }

    public function test_customer_cannot_manage_coupons(): void
    {
        $coupon = Coupon::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.coupons.index'))
            ->assertForbidden();

        $this->post(route('admin.coupons.store'), $this->validData())->assertForbidden();
        $this->delete(route('admin.coupons.destroy', $coupon))->assertForbidden();

        $this->assertModelExists($coupon);
    }
}
