<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// NFR-02: các mục kiểm tra bảo mật ở tài liệu kiểm thử buổi 8 mục 4
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    // Khi chạy test Laravel tự bỏ qua kiểm tra CSRF, nên bật lại cho các test cần đo 419
    private function enableCsrfCheck(): void
    {
        $this->app->singleton(PreventRequestForgery::class, fn ($app) => new class($app, $app['encrypter']) extends PreventRequestForgery
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
    }

    public function test_password_is_stored_as_bcrypt_hash(): void
    {
        $this->post(route('register'), [
            'name' => 'Nguyễn Văn A',
            'email' => 'nguyenvana@gmail.com',
            'phone' => '0912345678',
            'password' => 'matkhau123',
            'password_confirmation' => 'matkhau123',
        ]);

        $stored = User::where('email', 'nguyenvana@gmail.com')->value('password');

        $this->assertNotSame('matkhau123', $stored);
        $this->assertStringStartsWith('$2y$', $stored);
        $this->assertSame('bcrypt', Hash::info($stored)['algoName']);
    }

    public function test_form_without_csrf_token_returns_419(): void
    {
        $this->enableCsrfCheck();
        $user = User::factory()->create(['email' => 'khach@example.com']);

        $this->post(route('login'), ['email' => 'khach@example.com', 'password' => 'password'])
            ->assertStatus(419);
        $this->assertGuest();

        $product = Product::factory()->create(['stock' => 5]);
        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'receiver_name' => 'Nguyễn Văn A',
                'receiver_phone' => '0901234567',
                'shipping_address' => '12 Đường A, TP.HCM',
            ])
            ->assertStatus(419);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_form_with_valid_csrf_token_is_accepted(): void
    {
        $this->enableCsrfCheck();
        User::factory()->create(['email' => 'khach@example.com']);

        $this->withSession(['_token' => 'token-hop-le'])
            ->post(route('login'), ['_token' => 'token-hop-le', 'email' => 'khach@example.com', 'password' => 'password'])
            ->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_customer_gets_403_on_every_admin_page(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->create();

        $this->actingAs($customer);
        foreach (['admin.dashboard', 'admin.products.index', 'admin.categories.index', 'admin.orders.index', 'admin.users.index', 'admin.coupons.index'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->get(route('admin.orders.show', $order))->assertForbidden();
        $this->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])->assertForbidden();
        $this->patch(route('admin.users.lock', $customer))->assertForbidden();

        $this->assertFalse($customer->fresh()->is_locked);
    }

    public function test_sql_injection_in_shop_search_is_treated_as_text(): void
    {
        Product::factory()->create(['name' => 'Chuột Logitech M331']);
        Product::factory()->create(['name' => 'Tai nghe Sony']);

        $this->get(route('products.index', ['q' => "' OR 1=1 --"]))
            ->assertOk()
            ->assertSee('Không tìm thấy sản phẩm phù hợp')
            ->assertDontSee('Chuột Logitech M331')
            ->assertDontSee('Tai nghe Sony');

        $this->assertDatabaseCount('products', 2);
    }

    public function test_sql_injection_in_admin_search_is_treated_as_text(): void
    {
        Product::factory()->create(['name' => 'Chuột Logitech M331']);
        User::factory()->create(['name' => 'Trần Thị B']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['q' => "' OR 1=1 --"]))
            ->assertOk()
            ->assertDontSee('Chuột Logitech M331');

        $this->get(route('admin.users.index', ['q' => "' OR 1=1 --"]))
            ->assertOk()
            ->assertDontSee('Trần Thị B');

        $this->get(route('admin.orders.index', ['q' => "' OR 1=1 --"]))->assertOk();
    }
}
