<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tạo đơn 2 SP như sau khi đặt hàng: kho đã bị trừ, order_items lưu tên/giá lúc mua.
     */
    private function orderWithItems(User $user, OrderStatus $status = OrderStatus::Pending): Order
    {
        $order = Order::factory()->for($user)->status($status)->create(['subtotal' => 790000, 'total' => 790000]);

        $headphone = Product::factory()->create(['name' => 'Tai nghe Bluetooth X1', 'stock' => 8]);
        $cable = Product::factory()->create(['name' => 'Cáp sạc USB-C 1m', 'stock' => 4]);

        OrderItem::factory()->for($order)->for($headphone)->create(['price' => 350000, 'quantity' => 2, 'line_total' => 700000]);
        OrderItem::factory()->for($order)->for($cable)->create(['price' => 90000, 'quantity' => 1, 'line_total' => 90000]);

        return $order;
    }

    public function test_guest_cannot_view_orders(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_customer_sees_only_own_orders(): void
    {
        $user = User::factory()->create();
        $mine = Order::factory()->for($user)->create(['total' => 740000]);
        $other = Order::factory()->create();

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSeeInOrder([$mine->code, $mine->created_at->format('d/m/Y'), '740.000đ', 'Chờ xác nhận'])
            ->assertSee(route('orders.show', $mine->code), false)
            ->assertDontSee($other->code);
    }

    public function test_customer_can_filter_orders_by_status(): void
    {
        $user = User::factory()->create();
        $pending = Order::factory()->for($user)->create();
        $shipping = Order::factory()->for($user)->status(OrderStatus::Shipping)->create();

        $this->actingAs($user)
            ->get(route('orders.index', ['status' => 'shipping']))
            ->assertOk()
            ->assertSee($shipping->code)
            ->assertDontSee($pending->code);

        // Trạng thái lạ → hiện tất cả
        $this->get(route('orders.index', ['status' => 'abc']))
            ->assertOk()
            ->assertSee($shipping->code)
            ->assertSee($pending->code);
    }

    public function test_cancel_button_only_shows_for_pending_orders(): void
    {
        $user = User::factory()->create();
        $pending = Order::factory()->for($user)->create();
        $confirmed = Order::factory()->for($user)->status(OrderStatus::Confirmed)->create();

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertSee(route('orders.cancel', $pending->code), false)
            ->assertDontSee(route('orders.cancel', $confirmed->code), false);

        $this->get(route('orders.show', $confirmed->code))
            ->assertOk()
            ->assertDontSee(route('orders.cancel', $confirmed->code), false);
    }

    public function test_customer_sees_order_detail_with_prices_at_purchase(): void
    {
        $user = User::factory()->create();
        $order = $this->orderWithItems($user);
        $order->update(['receiver_name' => 'Nguyễn Văn A', 'receiver_phone' => '0901234567', 'note' => 'Giao giờ hành chính']);

        // Giá SP đổi sau khi đặt không làm đổi đơn cũ
        Product::where('name', 'Tai nghe Bluetooth X1')->update(['price' => 500000, 'sale_price' => null]);

        $this->actingAs($user)
            ->get(route('orders.show', $order->code))
            ->assertOk()
            ->assertSee('Đơn hàng '.$order->code)
            ->assertSee('Nguyễn Văn A · 0901234567')
            ->assertSee('Ghi chú: Giao giờ hành chính')
            ->assertSeeInOrder(['Tai nghe Bluetooth X1', '350.000đ', '2', '700.000đ'])
            ->assertSeeInOrder(['Cáp sạc USB-C 1m', '90.000đ', '1', '90.000đ'])
            ->assertSeeInOrder(['Tổng cộng', '790.000đ'])
            ->assertDontSee('500.000đ')
            ->assertSee(route('orders.cancel', $order->code), false);
    }

    public function test_customer_cannot_view_or_cancel_another_customers_order(): void
    {
        $order = $this->orderWithItems(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->get(route('orders.show', $order->code))
            ->assertForbidden();

        $this->patch(route('orders.cancel', $order->code))->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
        $this->get(route('orders.show', 'TS-9999'))->assertNotFound();
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_restored(): void
    {
        $user = User::factory()->create();
        $order = $this->orderWithItems($user);

        $this->actingAs($user)
            ->patch(route('orders.cancel', $order->code))
            ->assertRedirect(route('orders.show', $order->code))
            ->assertSessionHas('success', 'Đã hủy đơn '.$order->code.'.');

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(10, Product::where('name', 'Tai nghe Bluetooth X1')->value('stock'));
        $this->assertSame(5, Product::where('name', 'Cáp sạc USB-C 1m')->value('stock'));

        $this->get(route('orders.show', $order->code))
            ->assertOk()
            ->assertSee('Đơn hàng đã bị hủy.')
            ->assertDontSee(route('orders.cancel', $order->code), false);
    }

    public function test_customer_cannot_cancel_confirmed_order(): void
    {
        $user = User::factory()->create();
        $order = $this->orderWithItems($user, OrderStatus::Confirmed);

        $this->actingAs($user)
            ->from(route('orders.show', $order->code))
            ->patch(route('orders.cancel', $order->code))
            ->assertRedirect(route('orders.show', $order->code))
            ->assertSessionHas('error', 'Đơn '.$order->code.' đang ở trạng thái "Đã xác nhận", không thể hủy. Vui lòng liên hệ shop.');

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
        $this->assertSame(8, Product::where('name', 'Tai nghe Bluetooth X1')->value('stock'));
    }

    public function test_cancelled_order_cannot_be_cancelled_again(): void
    {
        $user = User::factory()->create();
        $order = $this->orderWithItems($user, OrderStatus::Cancelled);

        $this->actingAs($user)
            ->patch(route('orders.cancel', $order->code))
            ->assertSessionHas('error');

        // Không cộng kho lần 2
        $this->assertSame(8, Product::where('name', 'Tai nghe Bluetooth X1')->value('stock'));
    }
}
