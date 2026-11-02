<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cart_shows_message(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Giỏ hàng của bạn đang trống.')
            ->assertDontSee('Tiến hành đặt hàng');
    }

    public function test_guest_sees_cart_items_with_sale_price_and_totals(): void
    {
        $headphone = Product::factory()->create(['name' => 'Tai nghe Bluetooth X1', 'price' => 420000, 'sale_price' => 350000, 'stock' => 10]);
        $cable = Product::factory()->create(['name' => 'Cáp sạc USB-C 1m', 'price' => 90000, 'sale_price' => null, 'stock' => 5]);

        $this->withSession(['cart' => [$headphone->id => 2, $cable->id => 1]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSeeInOrder(['Tai nghe Bluetooth X1', '350.000đ', '700.000đ'])
            ->assertSeeInOrder(['Cáp sạc USB-C 1m', '90.000đ'])
            ->assertSeeInOrder(['Tạm tính:', '790.000đ', 'Tổng:', '790.000đ'])
            ->assertSee(route('checkout.index'), false);
    }

    public function test_opening_cart_reduces_quantity_over_stock(): void
    {
        $product = Product::factory()->create(['name' => 'Cáp sạc USB-C 1m', 'stock' => 1]);

        $this->withSession(['cart' => [$product->id => 3]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('chỉ còn 1, đã giảm số lượng trong giỏ.')
            ->assertSessionHas('cart', [$product->id => 1]);
    }

    public function test_opening_cart_removes_out_of_stock_and_hidden_products(): void
    {
        $soldOut = Product::factory()->outOfStock()->create(['name' => 'Chuột không dây M1']);
        $hidden = Product::factory()->hidden()->create();
        $available = Product::factory()->create(['stock' => 5]);

        $this->withSession(['cart' => [$soldOut->id => 1, $hidden->id => 2, $available->id => 1]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Sản phẩm &quot;Chuột không dây M1&quot; đã hết hàng, đã xóa khỏi giỏ.', false)
            ->assertSee('Một sản phẩm không còn được bán, đã xóa khỏi giỏ.')
            ->assertSessionHas('cart', [$available->id => 1]);
    }

    public function test_customer_can_update_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]])
            ->patch(route('cart.update', $product->id), ['quantity' => 4])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', 'Đã cập nhật số lượng.')
            ->assertSessionHas('cart', [$product->id => 4]);
    }

    public function test_update_over_stock_is_rejected(): void
    {
        $product = Product::factory()->create(['name' => 'Sạc dự phòng 10000mAh', 'stock' => 3]);

        $this->withSession(['cart' => [$product->id => 2]])
            ->patch(route('cart.update', $product->id), ['quantity' => 5])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', 'Sản phẩm "Sạc dự phòng 10000mAh" chỉ còn 3 trong kho.')
            ->assertSessionHas('cart', [$product->id => 2]);
    }

    public function test_update_with_invalid_quantity_fails_validation(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->withSession(['cart' => [$product->id => 2]])
            ->patch(route('cart.update', $product->id), ['quantity' => 0])
            ->assertSessionHasErrors('quantity')
            ->assertSessionHas('cart', [$product->id => 2]);
    }

    public function test_customer_can_remove_item(): void
    {
        $removed = Product::factory()->create();
        $kept = Product::factory()->create();

        $this->withSession(['cart' => [$removed->id => 1, $kept->id => 2]])
            ->delete(route('cart.remove', $removed->id))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', 'Đã xóa sản phẩm khỏi giỏ hàng.')
            ->assertSessionHas('cart', [$kept->id => 2]);
    }
}
