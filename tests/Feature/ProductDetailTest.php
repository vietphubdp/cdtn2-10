<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_page_shows_product_info_and_sale_price(): void
    {
        $category = Category::factory()->create(['name' => 'Tai nghe', 'slug' => 'tai-nghe']);
        $product = Product::factory()->for($category)->create([
            'name' => 'Tai nghe Bluetooth X1',
            'price' => 420000,
            'sale_price' => 350000,
            'stock' => 15,
            'description' => 'Pin 30 giờ, kết nối Bluetooth 5.3',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSeeInOrder(['Trang chủ', 'Tai nghe', 'Tai nghe Bluetooth X1'])
            ->assertSeeInOrder(['350.000đ', '420.000đ'])
            ->assertSee('Còn 15 sản phẩm')
            ->assertSee('Pin 30 giờ, kết nối Bluetooth 5.3')
            ->assertSee(route('products.index', ['category' => 'tai-nghe']), false);
    }

    public function test_in_stock_product_has_add_to_cart_form_limited_by_stock(): void
    {
        $product = Product::factory()->create(['stock' => 7]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Thêm vào giỏ hàng')
            ->assertSee('name="quantity"', false)
            ->assertSee('max="7"', false);
    }

    public function test_out_of_stock_product_is_shown_with_disabled_button(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Hết hàng')
            ->assertSee('disabled', false)
            ->assertDontSee('Thêm vào giỏ hàng')
            ->assertDontSee('name="quantity"', false);
    }

    public function test_hidden_product_returns_404(): void
    {
        $product = Product::factory()->hidden()->create();

        $this->get(route('products.show', $product->slug))
            ->assertNotFound()
            ->assertSee('Không tìm thấy trang hoặc sản phẩm');
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get(route('products.show', 'khong-ton-tai'))->assertNotFound();
    }

    public function test_unknown_url_shows_vietnamese_404_page(): void
    {
        $this->get('/trang-khong-ton-tai')
            ->assertNotFound()
            ->assertSee('Không tìm thấy trang hoặc sản phẩm');
    }

    public function test_detail_page_lists_reviews_with_average_rating(): void
    {
        $product = Product::factory()->create();
        $this->createReview($product, 'Nguyễn Văn A', 5, 'Âm thanh tốt, pin trâu.');
        $this->createReview($product, 'Trần Thị B', 4, 'Giao nhanh.');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('(2 đánh giá)')
            ->assertSee('★★★★★', false)
            ->assertSee('Nguyễn Văn A')
            ->assertSee('Âm thanh tốt, pin trâu.')
            ->assertSee('Trần Thị B')
            ->assertSee('Giao nhanh.');
    }

    public function test_detail_page_shows_message_when_no_reviews(): void
    {
        $product = Product::factory()->create();

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Sản phẩm chưa có đánh giá nào.');
    }

    private function createReview(Product $product, string $userName, int $rating, string $comment): Review
    {
        $user = User::factory()->create(['name' => $userName]);
        $order = Order::factory()->for($user)->status(OrderStatus::Completed)->create();

        return Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }
}
