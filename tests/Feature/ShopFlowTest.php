<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Chạy lại kịch bản demo Sprint 1 trên dữ liệu seed: admin thêm danh mục, sản phẩm có ảnh
// → khách đăng ký, lọc, xem chi tiết; khách bị chặn /admin; admin ẩn SP → SP biến mất
class ShopFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sprint1_demo_flow(): void
    {
        $this->seed();
        Storage::fake('public');

        // Admin đăng nhập → vào trang quản trị
        $this->post(route('login'), ['email' => 'admin@techshop.test', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->post(route('admin.categories.store'), ['name' => 'Giá đỡ điện thoại'])
            ->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Giá đỡ điện thoại', 'slug' => 'gia-do-dien-thoai']);
        $categoryId = Category::where('slug', 'gia-do-dien-thoai')->value('id');

        // Máy chưa bật GD nên không dùng fake()->image()
        $this->post(route('admin.products.store'), [
            'name' => 'Giá đỡ Baseus kẹp bàn',
            'category_id' => $categoryId,
            'price' => 250000,
            'sale_price' => 199000,
            'stock' => 10,
            'description' => 'Kẹp bàn, xoay 360 độ.',
            'is_active' => '1',
            'image' => UploadedFile::fake()->create('gia-do.jpg', 100, 'image/jpeg'),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Giá đỡ Baseus kẹp bàn')->firstOrFail();
        Storage::disk('public')->assertExists($product->image);

        $this->post(route('logout'));
        $this->assertGuest();

        // Khách đăng ký → đăng nhập luôn
        $this->post(route('register'), [
            'name' => 'Nguyễn Văn Demo',
            'email' => 'demo@techshop.test',
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));
        $customer = User::where('email', 'demo@techshop.test')->firstOrFail();
        $this->assertAuthenticatedAs($customer);

        // Lọc theo danh mục mới + tìm theo tên + khoảng giá (giá KM 199.000đ)
        $this->get(route('products.index', [
            'category' => 'gia-do-dien-thoai',
            'q' => 'Baseus',
            'min_price' => 150000,
            'max_price' => 200000,
        ]))
            ->assertOk()
            ->assertSee('Giá đỡ Baseus kẹp bàn')
            ->assertSee('199.000đ');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Giá đỡ Baseus kẹp bàn')
            ->assertSee('199.000đ')
            ->assertSee('250.000đ')
            ->assertSee($product->image_url);

        $this->get(route('admin.dashboard'))->assertForbidden();

        // Admin ẩn SP → khách không còn thấy
        $this->post(route('logout'));
        $this->actingAs(User::where('email', 'admin@techshop.test')->firstOrFail())
            ->patch(route('admin.products.active', $product))
            ->assertRedirect();
        $this->assertFalse($product->fresh()->is_active);

        $this->post(route('logout'));
        $this->get(route('products.index', ['category' => 'gia-do-dien-thoai']))
            ->assertOk()
            ->assertDontSee('Giá đỡ Baseus kẹp bàn');
        $this->get(route('products.show', $product->slug))->assertNotFound();
    }
}
