<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tên các SP trên trang danh sách theo đúng thứ tự hiển thị.
     */
    private function productNames(array $query): array
    {
        return $this->get(route('products.index', $query))
            ->assertOk()
            ->viewData('products')
            ->pluck('name')
            ->all();
    }

    public function test_search_by_name(): void
    {
        Product::factory()->create(['name' => 'Tai nghe Sony WH-CH520']);
        Product::factory()->create(['name' => 'Chuột Logitech M331']);

        $this->get(route('products.index', ['q' => 'sony']))
            ->assertOk()
            ->assertSee('Kết quả tìm kiếm "sony" (1 sản phẩm)')
            ->assertSee('Tai nghe Sony WH-CH520')
            ->assertDontSee('Chuột Logitech M331');
    }

    public function test_search_without_result_shows_message(): void
    {
        Product::factory()->create(['name' => 'Chuột Logitech M331']);

        $this->get(route('products.index', ['q' => 'không có sản phẩm này']))
            ->assertOk()
            ->assertSee('Không tìm thấy sản phẩm phù hợp');
    }

    public function test_search_does_not_show_inactive_products(): void
    {
        Product::factory()->hidden()->create(['name' => 'Loa JBL Flip 6']);

        $this->assertSame([], $this->productNames(['q' => 'JBL']));
    }

    public function test_filter_by_category(): void
    {
        $headphones = Category::factory()->create(['name' => 'Tai nghe', 'slug' => 'tai-nghe']);
        Product::factory()->for($headphones)->create(['name' => 'Tai nghe Sony WH-CH520']);
        Product::factory()->create(['name' => 'Chuột Logitech M331']);

        $this->get(route('products.index', ['category' => 'tai-nghe']))
            ->assertOk()
            ->assertSee('Tai nghe (1 sản phẩm)')
            ->assertSee('Tai nghe Sony WH-CH520')
            ->assertDontSee('Chuột Logitech M331');
    }

    public function test_unknown_category_shows_no_products(): void
    {
        Product::factory()->create();

        $this->get(route('products.index', ['category' => 'khong-ton-tai']))
            ->assertOk()
            ->assertSee('Không tìm thấy sản phẩm phù hợp');
    }

    public function test_filter_by_price_range_uses_sale_price(): void
    {
        Product::factory()->create(['name' => 'SP 150k', 'price' => 150000]);
        Product::factory()->create(['name' => 'SP 500k', 'price' => 500000]);
        // Giá gốc 1.200.000đ nhưng đang KM 900.000đ → nằm trong khoảng (BR-01)
        Product::factory()->create(['name' => 'SP KM 900k', 'price' => 1200000, 'sale_price' => 900000]);
        Product::factory()->create(['name' => 'SP 2tr', 'price' => 2000000]);

        $names = $this->productNames(['min_price' => 200000, 'max_price' => 1000000]);

        $this->assertEqualsCanonicalizing(['SP 500k', 'SP KM 900k'], $names);
    }

    public function test_invalid_price_is_ignored(): void
    {
        Product::factory()->count(2)->create();

        $this->assertCount(2, $this->productNames(['min_price' => 'abc', 'max_price' => -5]));
    }

    public function test_sort_by_price_ascending_and_descending(): void
    {
        Product::factory()->create(['name' => 'SP 500k', 'price' => 500000]);
        Product::factory()->create(['name' => 'SP KM 300k', 'price' => 800000, 'sale_price' => 300000]);
        Product::factory()->create(['name' => 'SP 700k', 'price' => 700000]);

        $this->assertSame(['SP KM 300k', 'SP 500k', 'SP 700k'], $this->productNames(['sort' => 'price_asc']));
        $this->assertSame(['SP 700k', 'SP 500k', 'SP KM 300k'], $this->productNames(['sort' => 'price_desc']));
    }

    public function test_default_sort_is_newest_first(): void
    {
        Product::factory()->create(['name' => 'SP cũ']);
        Product::factory()->create(['name' => 'SP mới']);

        $this->assertSame(['SP mới', 'SP cũ'], $this->productNames(['sort' => 'gia-tri-la']));
    }

    public function test_combined_filters_from_wireframe(): void
    {
        $headphones = Category::factory()->create(['slug' => 'tai-nghe']);
        Product::factory()->for($headphones)->create(['name' => 'Tai nghe A 350k', 'price' => 420000, 'sale_price' => 350000]);
        Product::factory()->for($headphones)->create(['name' => 'Tai nghe B 250k', 'price' => 250000]);
        Product::factory()->for($headphones)->create(['name' => 'Tai nghe C 150k', 'price' => 150000]);
        Product::factory()->create(['name' => 'Tai nghe khác danh mục 300k', 'price' => 300000]);

        $names = $this->productNames([
            'q' => 'tai',
            'category' => 'tai-nghe',
            'min_price' => 200000,
            'sort' => 'price_asc',
        ]);

        $this->assertSame(['Tai nghe B 250k', 'Tai nghe A 350k'], $names);
    }

    public function test_pagination_keeps_filters(): void
    {
        $headphones = Category::factory()->create(['slug' => 'tai-nghe']);
        Product::factory()->count(13)->for($headphones)->create(['price' => 300000]);

        $query = ['category' => 'tai-nghe', 'min_price' => 200000, 'sort' => 'price_asc'];

        $this->get(route('products.index', $query))
            ->assertOk()
            ->assertSee('category=tai-nghe&amp;min_price=200000&amp;sort=price_asc&amp;page=2', false);

        $this->assertCount(1, $this->productNames([...$query, 'page' => 2]));
    }

    public function test_filter_form_keeps_selected_values(): void
    {
        Category::factory()->create(['name' => 'Tai nghe', 'slug' => 'tai-nghe']);

        $this->get(route('products.index', [
            'q' => 'sony',
            'category' => 'tai-nghe',
            'min_price' => 200000,
            'max_price' => 1000000,
            'sort' => 'price_desc',
        ]))
            ->assertOk()
            ->assertSee('id="category-1" checked', false)
            ->assertSee('name="min_price" value="200000"', false)
            ->assertSee('name="max_price" value="1000000"', false)
            ->assertSee('<option value="price_desc" selected>', false)
            ->assertSee('<input type="hidden" name="q" value="sony">', false);
    }
}
