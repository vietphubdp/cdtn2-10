<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orders = new OrderService;
    }

    private function shipping(): array
    {
        return [
            'receiver_name' => 'Nguyễn Văn A',
            'receiver_phone' => '0912345678',
            'shipping_address' => '69/68 Đặng Thùy Trâm, Bình Thạnh, TP.HCM',
            'note' => 'Giao giờ hành chính',
        ];
    }

    public function test_place_order_saves_order_items_and_decreases_stock(): void
    {
        $user = User::factory()->create();
        $mouse = Product::factory()->create(['name' => 'Chuột A', 'price' => 300000, 'stock' => 10]);
        $speaker = Product::factory()->create(['name' => 'Loa B', 'price' => 500000, 'sale_price' => 450000, 'stock' => 5]);

        $order = $this->orders->placeOrder($user, $this->shipping(), [$mouse->id => 2, $speaker->id => 1]);

        $this->assertSame('TS-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT), $order->code);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(1050000, $order->subtotal);
        $this->assertSame(0, $order->discount);
        $this->assertSame(1050000, $order->total);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('Giao giờ hành chính', $order->note);

        // Lưu tên và giá lúc mua (giá KM nếu có)
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id, 'product_id' => $speaker->id,
            'product_name' => 'Loa B', 'price' => 450000, 'quantity' => 1, 'line_total' => 450000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id, 'product_id' => $mouse->id,
            'product_name' => 'Chuột A', 'price' => 300000, 'quantity' => 2, 'line_total' => 600000,
        ]);

        $this->assertSame(8, $mouse->fresh()->stock);
        $this->assertSame(4, $speaker->fresh()->stock);
    }

    public function test_order_keeps_old_price_after_product_changes(): void
    {
        $product = Product::factory()->create(['name' => 'Chuột A', 'price' => 300000]);
        $order = $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 1]);

        $product->update(['name' => 'Chuột A mới', 'price' => 350000]);

        $item = $order->items()->first();
        $this->assertSame('Chuột A', $item->product_name);
        $this->assertSame(300000, $item->price);
    }

    public function test_out_of_stock_rolls_back_whole_order(): void
    {
        $enough = Product::factory()->create(['stock' => 10]);
        $notEnough = Product::factory()->create(['name' => 'Loa B', 'stock' => 1]);

        try {
            $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$enough->id => 2, $notEnough->id => 2]);
            $this->fail('Phải báo lỗi khi thiếu hàng');
        } catch (BusinessException $e) {
            $this->assertStringContainsString('"Loa B" chỉ còn 1', $e->getMessage());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(10, $enough->fresh()->stock);
        $this->assertSame(1, $notEnough->fresh()->stock);
    }

    public function test_hidden_product_cannot_be_ordered(): void
    {
        $product = Product::factory()->hidden()->create();

        $this->expectException(BusinessException::class);
        $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 1]);
    }

    public function test_empty_cart_cannot_be_ordered(): void
    {
        $this->expectException(BusinessException::class);
        $this->orders->placeOrder(User::factory()->create(), $this->shipping(), []);
    }

    public function test_order_codes_follow_ids(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $first = $this->orders->placeOrder($user, $this->shipping(), [$product->id => 1]);
        $second = $this->orders->placeOrder($user, $this->shipping(), [$product->id => 1]);

        $this->assertMatchesRegularExpression('/^TS-\d{4,}$/', $first->code);
        $this->assertSame(Order::makeCode($first->id), $first->code);
        $this->assertSame(Order::makeCode($first->id + 1), $second->code);
    }
}
