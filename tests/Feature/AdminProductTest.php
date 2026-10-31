<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tai nghe Bluetooth X1',
            'category_id' => Category::factory()->create()->id,
            'price' => 350000,
            'sale_price' => '',
            'stock' => 15,
            'description' => 'Pin 30 giờ.',
            'is_active' => '1',
        ], $overrides);
    }

    // Máy chưa bật GD nên không dùng fake()->image()
    private function fakeImage(string $name = 'anh.jpg', int $kilobytes = 100, string $mime = 'image/jpeg'): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, $mime);
    }

    public function test_admin_sees_product_list(): void
    {
        $category = Category::factory()->create(['name' => 'Tai nghe']);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Tai nghe Sony A1', 'price' => 350000]);
        Product::factory()->hidden()->create(['name' => 'Ốp lưng iPhone A']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Tai nghe Sony A1')
            ->assertSee('350.000đ')
            ->assertSee('Ốp lưng iPhone A') // admin thấy cả SP đang ẩn
            ->assertSee(route('admin.products.create'));
    }

    public function test_customer_cannot_open_product_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.create'))
            ->assertForbidden();

        $this->post(route('admin.products.store'), $this->validData())->assertForbidden();
        $this->assertDatabaseCount('products', 0);
    }

    public function test_create_page_shows_form_with_categories(): void
    {
        Category::factory()->create(['name' => 'Sạc dự phòng']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Thêm sản phẩm')
            ->assertSee('Sạc dự phòng')
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('action="'.route('admin.products.store').'"', false);
    }

    public function test_admin_creates_product_with_image(): void
    {
        $data = $this->validData(['sale_price' => 299000, 'image' => $this->fakeImage()]);

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $data)
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $product = Product::firstOrFail();
        $this->assertSame('tai-nghe-bluetooth-x1', $product->slug);
        $this->assertSame(299000, $product->final_price);
        $this->assertTrue($product->is_active);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);

        // SP mới hiện ngay ở trang khách
        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Tai nghe Bluetooth X1');
    }

    public function test_unchecked_visibility_creates_hidden_product(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->validData(['is_active' => '0']))
            ->assertRedirect(route('admin.products.index'));

        $product = Product::firstOrFail();
        $this->assertFalse($product->is_active);
        $this->assertNull($product->image);
        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    public function test_duplicate_name_gets_numbered_slug(): void
    {
        Product::factory()->create(['name' => 'Chuột Logitech M1', 'slug' => 'chuot-logitech-m1']);

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->validData(['name' => 'Chuột Logitech M1']));

        $this->assertDatabaseHas('products', ['slug' => 'chuot-logitech-m1-2']);
    }

    public function test_sale_price_must_be_lower_than_price(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->validData(['price' => 300000, 'sale_price' => 300000]))
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors(['sale_price' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_required_fields_and_negative_stock_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->validData([
                'name' => '',
                'category_id' => 999,
                'price' => 'abc',
                'stock' => -1,
            ]))
            ->assertSessionHasErrors(['name', 'category_id', 'price', 'stock']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_image_must_be_jpg_or_png_up_to_2mb(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->validData(['image' => $this->fakeImage('tai-lieu.pdf', 100, 'application/pdf')]))
            ->assertSessionHasErrors('image');

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $this->validData(['image' => $this->fakeImage('to.jpg', 3000)]))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('products', 0);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_edit_page_shows_current_values(): void
    {
        $product = Product::factory()->create(['name' => 'Loa JBL Go 4', 'price' => 990000, 'stock' => 7]);

        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Sửa sản phẩm')
            ->assertSee('value="Loa JBL Go 4"', false)
            ->assertSee('value="990000"', false)
            ->assertSee('action="'.route('admin.products.update', $product).'"', false);
    }

    public function test_admin_updates_product_and_slug_follows_new_name(): void
    {
        $product = Product::factory()->create(['name' => 'Cáp Lightning', 'slug' => 'cap-lightning', 'is_active' => true]);
        $newCategory = Category::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), $this->validData([
                'name' => 'Cáp Lightning 2m',
                'category_id' => $newCategory->id,
                'price' => 120000,
                'sale_price' => 99000,
                'stock' => 0,
                'is_active' => '0',
            ]))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertSame('cap-lightning-2m', $product->slug);
        $this->assertSame($newCategory->id, $product->category_id);
        $this->assertSame(99000, $product->sale_price);
        $this->assertSame(0, $product->stock);
        $this->assertFalse($product->is_active);
    }

    public function test_update_keeping_name_keeps_slug(): void
    {
        $product = Product::factory()->create(['name' => 'Chuột M1', 'slug' => 'chuot-m1-cu']);

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), $this->validData(['name' => 'Chuột M1', 'price' => 200000]));

        $this->assertSame('chuot-m1-cu', $product->fresh()->slug);
        $this->assertSame(200000, $product->fresh()->price);
    }

    public function test_new_image_replaces_old_one(): void
    {
        Storage::disk('public')->put('products/cu.jpg', 'anh cu');
        $product = Product::factory()->create(['image' => 'products/cu.jpg']);

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), $this->validData(['image' => $this->fakeImage('moi.png', 100, 'image/png')]));

        $product->refresh();
        $this->assertNotSame('products/cu.jpg', $product->image);
        Storage::disk('public')->assertExists($product->image);
        Storage::disk('public')->assertMissing('products/cu.jpg');
    }

    public function test_update_without_new_image_keeps_old_one(): void
    {
        Storage::disk('public')->put('products/cu.jpg', 'anh cu');
        $product = Product::factory()->create(['image' => 'products/cu.jpg']);

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), $this->validData());

        $this->assertSame('products/cu.jpg', $product->fresh()->image);
        Storage::disk('public')->assertExists('products/cu.jpg');
    }

    public function test_admin_searches_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Tai nghe Sony A1']);
        Product::factory()->create(['name' => 'Cáp Lightning 1m']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['q' => 'sony']))
            ->assertOk()
            ->assertSee('Tai nghe Sony A1')
            ->assertDontSee('Cáp Lightning 1m');
    }

    public function test_admin_filters_by_category_and_status(): void
    {
        $headphones = Category::factory()->create();
        Product::factory()->create(['category_id' => $headphones->id, 'name' => 'Tai nghe đang bán']);
        Product::factory()->hidden()->create(['category_id' => $headphones->id, 'name' => 'Tai nghe ngừng bán']);
        Product::factory()->hidden()->create(['name' => 'Ốp lưng ngừng bán']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['category_id' => $headphones->id, 'status' => 'hidden']))
            ->assertOk()
            ->assertSee('Tai nghe ngừng bán')
            ->assertDontSee('Tai nghe đang bán')
            ->assertDontSee('Ốp lưng ngừng bán');
    }

    public function test_filter_is_kept_when_changing_page(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(21)->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['category_id' => $category->id]))
            ->assertOk()
            ->assertSee('category_id='.$category->id.'&amp;page=2', false);
    }

    public function test_invalid_filter_values_are_ignored(): void
    {
        Product::factory()->create(['name' => 'Tai nghe Sony A1']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['status' => 'abc', 'category_id' => 'xyz', 'q' => ['mang']]))
            ->assertOk()
            ->assertSee('Tai nghe Sony A1');
    }

    public function test_admin_hides_product_and_customer_no_longer_sees_it(): void
    {
        $product = Product::factory()->create(['name' => 'Tai nghe Sony A1']);

        $this->actingAs($this->admin())
            ->from(route('admin.products.index', ['q' => 'sony']))
            ->patch(route('admin.products.active', $product))
            ->assertRedirect(route('admin.products.index', ['q' => 'sony']))
            ->assertSessionHas('success');

        $this->assertFalse($product->fresh()->is_active);

        // BR-02: SP ẩn không có trên trang khách, mở link chi tiết → 404
        auth()->logout();
        $this->get(route('products.index'))->assertDontSee(route('products.show', $product->slug));
        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    public function test_admin_shows_hidden_product_again(): void
    {
        $product = Product::factory()->hidden()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.products.active', $product))
            ->assertSessionHas('success');

        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_admin_deletes_unsold_product_and_its_image(): void
    {
        Storage::disk('public')->put('products/anh.jpg', 'anh');
        $product = Product::factory()->create(['image' => 'products/anh.jpg']);

        $this->actingAs($this->admin())
            ->from(route('admin.products.index'))
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing('products/anh.jpg');
    }

    public function test_sold_product_has_no_delete_button_and_cannot_be_deleted(): void
    {
        $sold = Product::factory()->create();
        OrderItem::factory()->create(['product_id' => $sold->id]);
        $unsold = Product::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertSee('action="'.route('admin.products.destroy', $unsold).'"', false)
            ->assertDontSee('action="'.route('admin.products.destroy', $sold).'"', false);

        $this->delete(route('admin.products.destroy', $sold))
            ->assertSessionHas('error');

        $this->assertModelExists($sold);
    }

    public function test_customer_cannot_hide_or_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create());
        $this->patch(route('admin.products.active', $product))->assertForbidden();
        $this->delete(route('admin.products.destroy', $product))->assertForbidden();

        $this->assertTrue($product->fresh()->is_active);
        $this->assertModelExists($product);
    }
}
