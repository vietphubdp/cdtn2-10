<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// NFR-04: đơn đã đặt giữ nguyên tên, giá lúc mua dù admin sửa sản phẩm sau đó
class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_items_keep_old_price_after_admin_changes_product(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Chuột Logitech M331',
            'category_id' => $category->id,
            'price' => 300000,
            'sale_price' => 250000,
            'stock' => 10,
        ]);

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), [
                'receiver_name' => 'Nguyễn Văn A',
                'receiver_phone' => '0901234567',
                'shipping_address' => '12 Đường A, TP.HCM',
            ])
            ->assertRedirect();
        $order = Order::firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'Chuột Logitech M331 Silent',
                'category_id' => $category->id,
                'price' => 400000,
                'sale_price' => '',
                'stock' => 8,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.products.index'));
        $this->assertSame(400000, $product->fresh()->final_price);

        $item = $order->items()->first();
        $this->assertSame('Chuột Logitech M331', $item->product_name);
        $this->assertSame(250000, $item->price);
        $this->assertSame(500000, $order->fresh()->total);

        // Bỏ flash "Đã cập nhật sản phẩm …" của admin để không lẫn vào trang của khách
        $this->flushSession();
        $this->actingAs($customer)
            ->get(route('orders.show', $order->code))
            ->assertOk()
            ->assertSee('Chuột Logitech M331')
            ->assertDontSee('Silent')
            ->assertSee('250.000đ')
            ->assertSee('500.000đ')
            ->assertDontSee('400.000đ');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('250.000đ')
            ->assertDontSee('400.000đ');
    }
}
