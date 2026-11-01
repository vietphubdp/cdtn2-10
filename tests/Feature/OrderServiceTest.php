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

    public function test_cancel_pending_order_restores_stock(): void
    {
        $a = Product::factory()->create(['stock' => 10]);
        $b = Product::factory()->create(['stock' => 5]);
        $order = $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$a->id => 3, $b->id => 2]);

        $cancelled = $this->orders->cancel($order);

        $this->assertSame(OrderStatus::Cancelled, $cancelled->status);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(10, $a->fresh()->stock);
        $this->assertSame(5, $b->fresh()->stock);
    }

    public function test_admin_can_cancel_confirmed_order(): void
    {
        $product = Product::factory()->create(['stock' => 4]);
        $order = $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 1]);
        $order->update(['status' => OrderStatus::Confirmed]);

        $this->orders->cancel($order);

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_cannot_cancel_shipping_completed_or_cancelled_order(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 2]);

        foreach ([OrderStatus::Shipping, OrderStatus::Completed, OrderStatus::Cancelled] as $status) {
            $order->update(['status' => $status]);

            try {
                $this->orders->cancel($order);
                $this->fail('Không được hủy đơn '.$status->value);
            } catch (BusinessException) {
                $this->assertSame($status, $order->fresh()->status);
            }
        }

        // Không hoàn kho lần nào
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_cancel_uses_latest_status_from_database(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 2]);
        $stale = Order::find($order->id);

        $this->orders->cancel($order);

        // Bản $stale vẫn ghi pending nhưng đơn đã hủy → không được hoàn kho thêm lần nữa
        $this->expectException(BusinessException::class);
        try {
            $this->orders->cancel($stale);
        } finally {
            $this->assertSame(10, $product->fresh()->stock);
        }
    }

    public function test_change_status_follows_br05(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 1]);

        $this->orders->changeStatus($order, OrderStatus::Confirmed);
        $this->orders->changeStatus($order, OrderStatus::Shipping);
        $updated = $this->orders->changeStatus($order, OrderStatus::Completed);

        $this->assertSame(OrderStatus::Completed, $updated->status);
        $this->assertSame(9, $product->fresh()->stock);
    }

    public function test_change_status_blocks_skipping_steps(): void
    {
        $order = $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [Product::factory()->create()->id => 1]);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('từ "Chờ xác nhận" sang "Đang giao"');
        $this->orders->changeStatus($order, OrderStatus::Shipping);
    }

    public function test_change_status_to_cancelled_restores_stock(): void
    {
        $product = Product::factory()->create(['stock' => 6]);
        $order = $this->orders->placeOrder(User::factory()->create(), $this->shipping(), [$product->id => 2]);
        $this->orders->changeStatus($order, OrderStatus::Confirmed);

        $this->orders->changeStatus($order, OrderStatus::Cancelled);

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(6, $product->fresh()->stock);
    }
}
