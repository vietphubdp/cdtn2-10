<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_sees_categories_with_product_count(): void
    {
        $headphones = Category::factory()->create(['name' => 'Tai nghe', 'slug' => 'tai-nghe']);
        Category::factory()->create(['name' => 'Phụ kiện khác', 'slug' => 'phu-kien-khac']);
        Product::factory()->count(2)->create(['category_id' => $headphones->id]);
        Product::factory()->hidden()->create(['category_id' => $headphones->id]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Phụ kiện khác')
            ->assertSee('tai-nghe')
            ->assertSee('Thêm danh mục')
            ->assertSee('action="'.route('admin.categories.store').'"', false);

        $counts = $response->viewData('categories')->pluck('products_count', 'slug');
        $this->assertSame(3, $counts['tai-nghe']); // tính cả SP đang ẩn
        $this->assertSame(0, $counts['phu-kien-khac']);
    }

    public function test_create_page_redirects_to_list_with_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.categories.create'))
            ->assertRedirect(route('admin.categories.index'));
    }

    public function test_admin_can_create_category_with_auto_slug(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [
                'name' => 'Sạc dự phòng',
                'description' => 'Pin sạc cho điện thoại',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Sạc dự phòng',
            'slug' => 'sac-du-phong',
            'description' => 'Pin sạc cho điện thoại',
        ]);
    }

    public function test_slug_gets_number_when_already_used(): void
    {
        Category::factory()->create(['name' => 'Sạc & cáp', 'slug' => 'sac-cap']);

        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name' => 'Sạc cáp']);

        $this->assertDatabaseHas('categories', ['name' => 'Sạc cáp', 'slug' => 'sac-cap-2']);
    }

    public function test_name_is_required_and_unique(): void
    {
        Category::factory()->create(['name' => 'Tai nghe']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.categories.index'))
            ->post(route('admin.categories.store'), ['name' => ''])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors(['name' => 'Vui lòng nhập tên danh mục.']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Tai nghe'])
            ->assertSessionHasErrors(['name' => 'Tên danh mục đã được sử dụng.']);

        $this->assertSame(1, Category::count());
    }

    public function test_edit_page_shows_filled_form(): void
    {
        $category = Category::factory()->create(['name' => 'Chuột', 'description' => 'Chuột không dây']);

        $this->actingAs($this->admin())
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('Sửa danh mục')
            ->assertSee('action="'.route('admin.categories.update', $category).'"', false)
            ->assertSee('value="Chuột"', false)
            ->assertSee('Chuột không dây');
    }

    public function test_admin_can_rename_category_and_slug_follows(): void
    {
        $category = Category::factory()->create(['name' => 'Chuot', 'slug' => 'chuot']);

        $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), ['name' => 'Chuột gaming'])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $category->refresh();
        $this->assertSame('Chuột gaming', $category->name);
        $this->assertSame('chuot-gaming', $category->slug);
    }

    public function test_update_keeps_own_name_and_slug(): void
    {
        $category = Category::factory()->create(['name' => 'Bàn phím', 'slug' => 'ban-phim']);

        // Giữ nguyên tên, chỉ sửa mô tả → không báo trùng tên, slug không đổi
        $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), ['name' => 'Bàn phím', 'description' => 'Bàn phím cơ'])
            ->assertSessionHasNoErrors();

        $category->refresh();
        $this->assertSame('ban-phim', $category->slug);
        $this->assertSame('Bàn phím cơ', $category->description);
    }

    public function test_update_rejects_name_of_other_category(): void
    {
        Category::factory()->create(['name' => 'Tai nghe']);
        $category = Category::factory()->create(['name' => 'Loa']);

        $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), ['name' => 'Tai nghe'])
            ->assertSessionHasErrors('name');

        $this->assertSame('Loa', $category->fresh()->name);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($category);
    }

    public function test_cannot_delete_category_that_still_has_products(): void
    {
        $category = Category::factory()->create(['name' => 'Tai nghe']);
        Product::factory()->count(2)->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error', 'Không thể xóa "Tai nghe": danh mục còn 2 sản phẩm.');

        $this->assertModelExists($category);
    }

    public function test_cannot_delete_category_with_only_hidden_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->hidden()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_delete_button_asks_for_confirmation(): void
    {
        Category::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.categories.index'))
            ->assertSee('onsubmit="return confirm(', false)
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_customer_and_guest_cannot_manage_categories(): void
    {
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.categories.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $category))
            ->assertForbidden();

        $this->assertModelExists($category);
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->post(route('admin.categories.store'), ['name' => 'Ốp lưng'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('categories', ['name' => 'Ốp lưng']);
    }
}
