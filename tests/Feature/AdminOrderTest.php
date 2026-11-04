<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

    /**
     * Đơn 1 sản phẩm: kho còn $stock sau khi đã bán $quantity.
     */
    private function orderWithProduct(OrderStatus $status, int $stock = 5, int $quantity = 2): Order
    {
        $order = Order::factory()->status($status)->create();
        OrderItem::factory()->for($order)->create([
            'product_id' => Product::factory()->create(['stock' => $stock])->id,
            'quantity' => $quantity,
        ]);

        return $order;
    }

    public function test_admin_sees_order_detail_with_items_and_next_statuses(): void
    {
        $order = Order::factory()->create([
            'receiver_name' => 'Nguyễn Văn An',
            'receiver_phone' => '0912345678',
            'shipping_address' => '12 Đường A, TP.HCM',
            'note' => 'giao giờ hành chính',
            'subtotal' => 790000,
            'total' => 790000,
        ]);
        OrderItem::factory()->for($order)->create([
            'product_name' => 'Tai nghe Bluetooth X1',
            'price' => 350000,
            'quantity' => 2,
            'line_total' => 700000,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Đơn hàng TS-0001')
            ->assertSee('Nguyễn Văn An · 0912345678')
            ->assertSee('Ghi chú: giao giờ hành chính')
            ->assertSee('Tai nghe Bluetooth X1')
            ->assertSee('700.000đ')
            ->assertSee('790.000đ')
            // BR-05: đơn chờ xác nhận chỉ chọn được "Đã xác nhận" hoặc "Đã hủy"
            ->assertSee('<option value="confirmed"', false)
            ->assertSee('<option value="cancelled"', false)
            ->assertDontSee('<option value="shipping"', false)
            ->assertDontSee('<option value="completed"', false);
    }

    public function test_discount_row_only_shown_when_order_has_discount(): void
    {
        $admin = $this->admin();
        $plain = Order::factory()->create(['subtotal' => 500000, 'discount' => 0, 'total' => 500000]);
        $discounted = Order::factory()->create(['subtotal' => 500000, 'discount' => 50000, 'total' => 450000]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $plain))
            ->assertOk()
            ->assertDontSee('−0đ');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $discounted))
            ->assertOk()
            ->assertSee('−50.000đ')
            ->assertSee('450.000đ');
    }

    public function test_final_status_order_has_no_update_form(): void
    {
        $order = Order::factory()->status(OrderStatus::Completed)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('không thể đổi nữa')
            ->assertDontSee('name="status"', false);
    }

    public function test_admin_moves_order_through_valid_statuses(): void
    {
        $order = Order::factory()->create();
        $admin = $this->admin();

        foreach ([OrderStatus::Confirmed, OrderStatus::Shipping, OrderStatus::Completed] as $status) {
            $this->actingAs($admin)
                ->patch(route('admin.orders.status', $order), ['status' => $status->value])
                ->assertRedirect(route('admin.orders.show', $order))
                ->assertSessionHas('success', 'Đã chuyển đơn TS-0001 sang "'.$status->label().'".');

            $this->assertSame($status, $order->fresh()->status);
        }
    }

    public function test_invalid_transition_is_blocked(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'completed'])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error', 'Không thể chuyển đơn TS-0001 từ "Chờ xác nhận" sang "Hoàn thành".');

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_cancelling_order_restores_stock(): void
    {
        $order = $this->orderWithProduct(OrderStatus::Confirmed, stock: 5, quantity: 2);

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('success', 'Đã chuyển đơn TS-0001 sang "Đã hủy". Tồn kho đã được cộng lại.');

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(7, $order->items->first()->product->fresh()->stock);
    }

    public function test_shipping_order_cannot_be_cancelled_and_stock_unchanged(): void
    {
        $order = $this->orderWithProduct(OrderStatus::Shipping, stock: 5, quantity: 2);

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::Shipping, $order->fresh()->status);
        $this->assertSame(5, $order->items->first()->product->fresh()->stock);
    }

    public function test_unknown_status_value_is_rejected(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => 'abc'])
            ->assertSessionHasErrors(['status' => 'Trạng thái không hợp lệ.']);

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_customer_cannot_view_or_update_admin_order(): void
    {
        $order = Order::factory()->create();
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.orders.show', $order))
            ->assertForbidden();

        $this->actingAs($customer)
            ->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }
}
