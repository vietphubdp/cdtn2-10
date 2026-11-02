<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_sees_order_list_with_customer_and_total(): void
    {
        Order::factory()->create([
            'receiver_name' => 'Nguyễn Văn An',
            'receiver_phone' => '0912345678',
            'subtotal' => 740000,
            'total' => 740000,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('TS-0001')
            ->assertSee('Nguyễn Văn An · 0912345678')
            ->assertSee('740.000đ')
            ->assertSee('Chờ xác nhận');
    }

    public function test_newest_order_is_listed_first(): void
    {
        Order::factory()->create();
        Order::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertSeeInOrder(['TS-0002', 'TS-0001']);
    }

    public function test_list_shows_20_orders_per_page(): void
    {
        Order::factory()->count(21)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertSee('TS-0021')
            ->assertSee('TS-0002')
            ->assertDontSee('TS-0001');
    }

    public function test_admin_filters_orders_by_status(): void
    {
        Order::factory()->status(OrderStatus::Pending)->create();
        Order::factory()->status(OrderStatus::Completed)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('TS-0002')
            ->assertDontSee('TS-0001');
    }

    public function test_status_tabs_show_order_count(): void
    {
        Order::factory()->count(2)->status(OrderStatus::Pending)->create();
        Order::factory()->status(OrderStatus::Shipping)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertSee('Tất cả (3)')
            ->assertSee('Chờ xác nhận (2)')
            ->assertSee('Đang giao (1)')
            ->assertSee('Đã hủy (0)');
    }

    public function test_admin_searches_order_by_code(): void
    {
        Order::factory()->count(3)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['code' => '0002']))
            ->assertSee('TS-0002')
            ->assertDontSee('TS-0001')
            ->assertDontSee('TS-0003')
            ->assertSee('Tất cả (1)'); // số đếm theo bộ lọc mã đơn
    }

    public function test_admin_filters_orders_by_date_range(): void
    {
        Order::factory()->create(['created_at' => '2026-10-01 10:00:00']);
        Order::factory()->create(['created_at' => '2026-10-15 23:30:00']);
        Order::factory()->create(['created_at' => '2026-10-20 08:00:00']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['from' => '2026-10-10', 'to' => '2026-10-15']))
            ->assertSee('TS-0002') // tính trọn ngày "đến ngày"
            ->assertDontSee('TS-0001')
            ->assertDontSee('TS-0003');
    }

    public function test_invalid_filters_are_ignored(): void
    {
        Order::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['status' => 'abc', 'from' => '2026-13-45', 'to' => 'hom-qua']))
            ->assertOk()
            ->assertSee('TS-0001')
            ->assertSee('TS-0002');
    }

    public function test_empty_filter_result_shows_message(): void
    {
        Order::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['code' => 'TS-9999']))
            ->assertSee('Không có đơn hàng phù hợp bộ lọc.');
    }

    public function test_customer_cannot_open_admin_order_list(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.orders.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.orders.index'))
            ->assertRedirect(route('login'));
    }
}
