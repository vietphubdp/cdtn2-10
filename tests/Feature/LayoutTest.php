<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_header_nav_and_footer(): void
    {
        Category::factory()->create(['name' => 'Tai nghe', 'slug' => 'tai-nghe']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('TECHSHOP')
            ->assertSee('Tìm sản phẩm theo tên...')
            ->assertSee('Đăng nhập / Đăng ký')
            ->assertSee('Giỏ hàng')
            ->assertSee('Tai nghe')
            ->assertSee(route('products.index', ['category' => 'tai-nghe']), false)
            ->assertSee('Hotline');
    }

    public function test_logged_in_customer_sees_account_menu(): void
    {
        $customer = User::factory()->create(['name' => 'Nguyễn Văn A']);

        $this->actingAs($customer)
            ->view('partials.header')
            ->assertSee('Nguyễn Văn A')
            ->assertSee('Đơn hàng của tôi')
            ->assertSee('Đăng xuất')
            ->assertDontSee('Trang quản trị');
    }

    public function test_admin_sees_link_to_admin_page(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->view('partials.header')
            ->assertSee('Trang quản trị');
    }

    public function test_header_shows_total_quantity_in_cart(): void
    {
        session(['cart' => [1 => 2, 5 => 3]]);

        $this->view('partials.header')->assertSeeInOrder(['Giỏ hàng', '5']);
    }

    public function test_home_page_uses_customer_layout(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('TECHSHOP')
            ->assertSee('Hotline');
    }

    public function test_flash_messages_are_displayed(): void
    {
        $this->withSession(['success' => 'Đã lưu thành công'])
            ->get(route('login'))
            ->assertSee('Đã lưu thành công');
    }

    public function test_product_card_shows_sale_price_and_original_price(): void
    {
        $product = Product::factory()->create(['price' => 420000, 'sale_price' => 350000]);

        $this->view('partials.product-card', ['product' => $product])
            ->assertSee('350.000đ')
            ->assertSee('420.000đ')
            ->assertSee('Thêm vào giỏ')
            ->assertSee(route('products.show', $product->slug), false);
    }

    public function test_product_card_disables_button_when_out_of_stock(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->view('partials.product-card', ['product' => $product])
            ->assertSee('Hết hàng')
            ->assertDontSee('Thêm vào giỏ');
    }
}
