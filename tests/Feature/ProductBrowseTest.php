<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_new_and_sale_products(): void
    {
        Product::factory()->create(['name' => 'Chuột Logitech M331']);
        Product::factory()->create(['name' => 'Tai nghe Sony WH-CH520', 'price' => 1290000, 'sale_price' => 990000]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Sản phẩm mới', 'Chuột Logitech M331', 'Đang khuyến mãi', 'Tai nghe Sony WH-CH520'])
            ->assertSee('990.000đ')
            ->assertSee('1.290.000đ');
    }

    public function test_home_page_shows_at_most_8_newest_products(): void
    {
        $oldest = Product::factory()->create();
        Product::factory()->count(8)->create();

        $this->get(route('home'))
            ->assertViewHas('newProducts', fn ($products) => $products->count() === 8
                && ! $products->contains($oldest));
    }

    public function test_sale_section_only_has_products_with_sale_price(): void
    {
        $sale = Product::factory()->onSale()->create();
        $normal = Product::factory()->create();

        $this->get(route('home'))
            ->assertViewHas('saleProducts', fn ($products) => $products->contains($sale)
                && ! $products->contains($normal));
    }

    public function test_home_page_hides_inactive_products(): void
    {
        Product::factory()->hidden()->onSale()->create(['name' => 'Loa JBL đang ẩn']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Loa JBL đang ẩn');
    }

    public function test_home_page_counts_active_products_per_category(): void
    {
        $category = Category::factory()->create(['name' => 'Tai nghe', 'slug' => 'tai-nghe']);
        Product::factory()->count(3)->for($category)->create();
        Product::factory()->hidden()->for($category)->create();

        $this->get(route('home'))
            ->assertSee('Tai nghe (3)')
            ->assertSee(route('products.index', ['category' => 'tai-nghe']), false);
    }

    public function test_product_list_paginates_12_products_per_page(): void
    {
        Product::factory()->count(13)->create();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Tất cả sản phẩm (13 sản phẩm)')
            ->assertViewHas('products', fn ($products) => $products->count() === 12);

        $this->get(route('products.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->count() === 1);
    }

    public function test_product_list_hides_inactive_products(): void
    {
        Product::factory()->create(['name' => 'Bàn phím Akko đang bán']);
        Product::factory()->hidden()->create(['name' => 'Bàn phím E-Dra đang ẩn']);

        $this->get(route('products.index'))
            ->assertSee('Bàn phím Akko đang bán')
            ->assertDontSee('Bàn phím E-Dra đang ẩn');
    }

    public function test_product_list_shows_message_when_empty(): void
    {
        Product::factory()->hidden()->create();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Không tìm thấy sản phẩm phù hợp');
    }
}
