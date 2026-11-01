<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cart = new CartService;
    }

    public function test_add_stores_quantity_in_session_and_accumulates(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->cart->add($product, 2);
        $this->cart->add($product, 3);

        $this->assertSame([$product->id => 5], session('cart'));
        $this->assertSame(5, $this->cart->count());
    }

    public function test_subtotal_uses_final_price(): void
    {
        $normal = Product::factory()->create(['price' => 300000, 'stock' => 10]);
        $onSale = Product::factory()->create(['price' => 500000, 'sale_price' => 400000, 'stock' => 10]);

        $this->cart->add($normal, 2);
        $this->cart->add($onSale, 1);

        $items = $this->cart->items();
        $this->assertCount(2, $items);
        $this->assertSame(400000, $items[1]['line_total']);
        $this->assertSame(1000000, $this->cart->subtotal());
    }

    public function test_cannot_add_more_than_stock(): void
    {
        $product = Product::factory()->create(['stock' => 3]);
        $this->cart->add($product, 2);

        try {
            $this->cart->add($product, 2);
            $this->fail('Phải báo lỗi khi vượt tồn kho');
        } catch (BusinessException $e) {
            $this->assertStringContainsString('chỉ còn 3', $e->getMessage());
        }

        $this->assertSame([$product->id => 2], session('cart'));
    }

    public function test_cannot_add_out_of_stock_product(): void
    {
        $this->expectException(BusinessException::class);
        $this->cart->add(Product::factory()->outOfStock()->create());
    }

    public function test_cannot_add_hidden_product(): void
    {
        $this->expectException(BusinessException::class);
        $this->cart->add(Product::factory()->hidden()->create());
    }

    public function test_update_changes_quantity_and_checks_stock(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->cart->add($product);

        $this->cart->update($product->id, 4);
        $this->assertSame([$product->id => 4], session('cart'));

        $this->expectException(BusinessException::class);
        $this->cart->update($product->id, 6);
    }

    public function test_update_to_zero_removes_line(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->cart->add($product, 2);

        $this->cart->update($product->id, 0);

        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_remove_and_clear(): void
    {
        $a = Product::factory()->create();
        $b = Product::factory()->create();
        $this->cart->add($a);
        $this->cart->add($b);

        $this->cart->remove($a->id);
        $this->assertSame([$b->id => 1], session('cart'));

        $this->cart->clear();
        $this->assertTrue($this->cart->isEmpty());
        $this->assertSame(0, $this->cart->subtotal());
    }

    public function test_items_skip_product_hidden_after_adding(): void
    {
        $product = Product::factory()->create();
        $this->cart->add($product);

        $product->update(['is_active' => false]);

        $this->assertCount(0, $this->cart->items());
    }

    public function test_sync_with_stock_reduces_or_removes_lines(): void
    {
        $reduced = Product::factory()->create(['name' => 'Chuột A', 'stock' => 10]);
        $soldOut = Product::factory()->create(['name' => 'Loa B', 'stock' => 10]);
        $hidden = Product::factory()->create(['stock' => 10]);
        $fine = Product::factory()->create(['stock' => 10]);
        $this->cart->add($reduced, 8);
        $this->cart->add($soldOut, 1);
        $this->cart->add($hidden, 1);
        $this->cart->add($fine, 2);

        // Người khác mua làm tồn kho giảm, admin ẩn 1 SP
        $reduced->update(['stock' => 3]);
        $soldOut->update(['stock' => 0]);
        $hidden->update(['is_active' => false]);

        $messages = $this->cart->syncWithStock();

        $this->assertSame([$reduced->id => 3, $fine->id => 2], session('cart'));
        $this->assertCount(3, $messages);
        $this->assertStringContainsString('"Chuột A" chỉ còn 3', $messages[0]);
        $this->assertStringContainsString('"Loa B" đã hết hàng', $messages[1]);
    }

    public function test_sync_with_stock_returns_no_message_when_cart_is_valid(): void
    {
        $this->cart->add(Product::factory()->create(['stock' => 5]), 2);

        $this->assertSame([], $this->cart->syncWithStock());
    }
}
