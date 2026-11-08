<?php

namespace Tests\Feature;

use App\Mail\OrderPlacedMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class OrderPlacedMailTest extends TestCase
{
    use RefreshDatabase;

    private function shipping(): array
    {
        return [
            'receiver_name' => 'Nguyễn Văn A',
            'receiver_phone' => '0901234567',
            'shipping_address' => '12 Đường A, TP.HCM',
        ];
    }

    public function test_successful_order_sends_one_mail_to_customer(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'khach@example.com']);
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->shipping());

        $order = Order::sole();
        Mail::assertSent(OrderPlacedMail::class, 1);
        Mail::assertSent(OrderPlacedMail::class, fn (OrderPlacedMail $mail) => $mail->hasTo('khach@example.com') && $mail->order->is($order));
    }

    public function test_failed_order_sends_no_mail(): void
    {
        Mail::fake();
        $product = Product::factory()->create(['stock' => 1]);

        // Hết hàng giữa chừng → không có đơn, không gửi mail
        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 3]])
            ->post(route('checkout.store'), $this->shipping())
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
    }

    public function test_mail_error_does_not_break_order(): void
    {
        Exceptions::fake();
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP không kết nối được'));
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), $this->shipping());

        $order = Order::sole();
        $response->assertRedirect(route('checkout.success', $order->code))
            ->assertSessionMissing('cart');
        $this->assertSame(3, $product->fresh()->stock);
        Exceptions::assertReported(RuntimeException::class);
    }

    public function test_mail_shows_order_details_and_link(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'GIAM10']);
        $order = Order::factory()->create([
            'coupon_id' => $coupon->id,
            'receiver_name' => 'Trần Thị B',
            'subtotal' => 900000,
            'discount' => 90000,
            'total' => 810000,
        ]);
        $order->items()->create([
            'product_id' => Product::factory()->create()->id,
            'product_name' => 'Tai nghe Bluetooth X1',
            'price' => 450000,
            'quantity' => 2,
            'line_total' => 900000,
        ]);

        $mail = new OrderPlacedMail($order);

        $mail->assertHasSubject('Xác nhận đơn hàng '.$order->code.' – '.config('app.name'));
        $mail->assertSeeInHtml('Trần Thị B');
        $mail->assertSeeInOrderInHtml(['Tai nghe Bluetooth X1', '450.000đ', '2', '900.000đ']);
        $mail->assertSeeInOrderInHtml(['Giảm giá (GIAM10)', '90.000đ', 'Tổng thanh toán', '810.000đ']);
        $mail->assertSeeInHtml(route('orders.show', $order->code));
    }
}
