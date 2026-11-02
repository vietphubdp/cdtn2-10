<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function shipping(array $overrides = []): array
    {
        return array_merge([
            'receiver_name' => 'Nguyễn Văn A',
            'receiver_phone' => '0901234567',
            'shipping_address' => '12 Đường A, TP.HCM',
            'note' => 'Giao giờ hành chính',
        ], $overrides);
    }

    public function test_guest_is_sent_to_login_and_back_to_checkout(): void
    {
        $user = User::factory()->create(['email' => 'khach@example.com']);
        $product = Product::factory()->create(['stock' => 5]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.index'))
            ->assertRedirect(route('login'));

        // Giỏ vẫn còn sau khi đăng nhập, quay lại đúng trang thanh toán
        $this->post(route('login'), ['email' => 'khach@example.com', 'password' => 'password'])
            ->assertRedirect(route('checkout.index'));

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSessionHas('cart', [$product->id => 1]);
    }

    public function test_checkout_form_is_prefilled_from_profile(): void
    {
        $user = User::factory()->create(['name' => 'Trần Thị B', 'phone' => '0912345678', 'address' => '45 Lê Lợi, Quận 1']);
        $product = Product::factory()->create(['name' => 'Tai nghe Bluetooth X1', 'price' => 420000, 'sale_price' => 350000, 'stock' => 10]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('value="Trần Thị B"', false)
            ->assertSee('value="0912345678"', false)
            ->assertSee('value="45 Lê Lợi, Quận 1"', false)
            ->assertSeeInOrder(['Tai nghe Bluetooth X1 × 2', '700.000đ'])
            ->assertSeeInOrder(['Tổng cộng:', '700.000đ'])
            ->assertSee('Thanh toán khi nhận hàng (COD)');
    }

    public function test_checkout_with_empty_cart_redirects_to_cart(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Giỏ hàng đang trống, hãy chọn sản phẩm trước khi đặt hàng.');

        $this->post(route('checkout.store'), $this->shipping())
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Giỏ hàng đang trống.');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_opening_checkout_rechecks_stock(): void
    {
        $product = Product::factory()->create(['name' => 'Cáp sạc USB-C 1m', 'stock' => 1]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 3]])
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Sản phẩm "Cáp sạc USB-C 1m" chỉ còn 1, đã giảm số lượng trong giỏ.')
            ->assertSessionHas('cart', [$product->id => 1]);
    }

    public function test_customer_places_order_successfully(): void
    {
        $user = User::factory()->create();
        $headphone = Product::factory()->create(['name' => 'Tai nghe Bluetooth X1', 'price' => 420000, 'sale_price' => 350000, 'stock' => 10]);
        $cable = Product::factory()->create(['name' => 'Cáp sạc USB-C 1m', 'price' => 90000, 'stock' => 5]);

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$headphone->id => 2, $cable->id => 1]])
            ->post(route('checkout.store'), $this->shipping());

        $order = Order::sole();
        $response->assertRedirect(route('checkout.success', $order->code))
            ->assertSessionMissing('cart');

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(790000, $order->total);
        $this->assertSame('Giao giờ hành chính', $order->note);
        $this->assertSame(8, $headphone->fresh()->stock);
        $this->assertSame(4, $cable->fresh()->stock);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_name' => 'Tai nghe Bluetooth X1', 'price' => 350000, 'quantity' => 2]);
    }

    public function test_success_page_shows_order_summary(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 740000, 'stock' => 3]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->shipping());

        $order = Order::sole();

        $this->get(route('checkout.success', $order->code))
            ->assertOk()
            ->assertSee('Đặt hàng thành công!')
            ->assertSee($order->code)
            ->assertSee('Chờ xác nhận')
            ->assertSee('740.000đ')
            ->assertSee('Nguyễn Văn A · 0901234567 · 12 Đường A, TP.HCM')
            ->assertSee(route('orders.show', $order->code), false);
    }

    public function test_out_of_stock_during_checkout_rolls_back(): void
    {
        $user = User::factory()->create();
        $headphone = Product::factory()->create(['stock' => 10]);
        $cable = Product::factory()->create(['name' => 'Cáp sạc USB-C 1m', 'stock' => 5]);
        $cart = [$headphone->id => 2, $cable->id => 3];

        // Khách khác mua hết cáp sau khi khách này đã mở trang thanh toán
        $cable->update(['stock' => 1]);

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), $this->shipping())
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Sản phẩm "Cáp sạc USB-C 1m" chỉ còn 1 trong kho, vui lòng cập nhật giỏ hàng.')
            ->assertSessionHas('cart', $cart);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(10, $headphone->fresh()->stock);
        $this->assertSame(1, $cable->fresh()->stock);
    }

    public function test_invalid_shipping_info_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]])
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), $this->shipping([
                'receiver_name' => '',
                'receiver_phone' => '12345',
                'shipping_address' => '',
            ]))
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors(['receiver_name', 'receiver_phone', 'shipping_address']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_customer_cannot_view_success_page_of_another_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('checkout.success', $order->code))
            ->assertForbidden();

        $this->get(route('checkout.success', 'TS-9999'))->assertNotFound();
    }
}
