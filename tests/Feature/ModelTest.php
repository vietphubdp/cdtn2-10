<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_price_uses_sale_price_when_present(): void
    {
        $product = Product::factory()->create(['price' => 500000, 'sale_price' => 400000]);
        $this->assertSame(400000, $product->final_price);

        $product = Product::factory()->create(['price' => 500000, 'sale_price' => null]);
        $this->assertSame(500000, $product->final_price);
    }

    public function test_on_sale_state_is_cheaper_than_price(): void
    {
        $product = Product::factory()->onSale()->create();

        $this->assertLessThan($product->price, $product->sale_price);
    }

    public function test_active_scope_hides_inactive_products(): void
    {
        Product::factory()->count(2)->create();
        Product::factory()->hidden()->create();

        $this->assertSame(2, Product::active()->count());
    }

    public function test_cannot_delete_category_that_still_has_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $this->expectException(QueryException::class);
        $category->delete();
    }

    public function test_order_gets_code_and_status_enum(): void
    {
        $order = Order::factory()->create();

        $this->assertSame(Order::makeCode($order->id), $order->fresh()->code);
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_order_item_copies_product_name_and_price(): void
    {
        $product = Product::factory()->create(['price' => 300000, 'sale_price' => 250000]);
        $item = OrderItem::factory()->for($product)->create(['quantity' => 2]);

        $this->assertSame($product->name, $item->product_name);
        $this->assertSame(250000, $item->price);
        $this->assertSame(500000, $item->line_total);
    }
}
