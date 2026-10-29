<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create(['name' => 'Quản trị viên']);
    }

    public function test_admin_sees_sidebar_with_all_menu_items(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('TECHSHOP ADMIN')
            ->assertSee('Xin chào, Quản trị viên')
            ->assertSeeInOrder(['Dashboard', 'Danh mục', 'Sản phẩm', 'Đơn hàng', 'Người dùng', 'Mã giảm giá', 'Đăng xuất'])
            ->assertSee(route('admin.categories.index'), false)
            ->assertSee(route('admin.products.index'), false)
            ->assertSee(route('admin.orders.index'), false)
            ->assertSee(route('admin.users.index'), false);
    }

    public function test_current_menu_item_is_highlighted(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertSee('href="'.route('admin.dashboard').'"', false)
            ->assertSeeInOrder(['aria-current="page"', 'Dashboard'], false);
    }

    public function test_logout_in_sidebar_is_post_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_sidebar_collapses_on_small_screen(): void
    {
        // Nút ☰ mở sidebar dạng offcanvas ở màn hình nhỏ (360px)
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertSee('data-bs-target="#adminSidebar"', false)
            ->assertSee('offcanvas-lg', false);
    }

    public function test_dashboard_shows_counts(): void
    {
        $category = Category::factory()->create();
        Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        User::factory()->count(4)->create();

        // Tài khoản admin không tính là khách hàng
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertViewHas('categoryCount', 2)
            ->assertViewHas('productCount', 3)
            ->assertViewHas('orderCount', 0)
            ->assertViewHas('customerCount', 4);
    }

    public function test_admin_layout_shows_flash_message(): void
    {
        $this->actingAs($this->admin())
            ->withSession(['success' => 'Đã lưu danh mục.'])
            ->get(route('admin.dashboard'))
            ->assertSee('Đã lưu danh mục.');
    }
}
