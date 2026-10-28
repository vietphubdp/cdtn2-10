<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function validRegisterData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nguyễn Văn A',
            'email' => 'nguyenvana@gmail.com',
            'phone' => '0912345678',
            'password' => 'matkhau123',
            'password_confirmation' => 'matkhau123',
        ], $overrides);
    }

    // ---------- Đăng ký (FR-01) [T-03] ----------

    public function test_register_page_is_displayed(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Đăng ký tài khoản');
    }

    public function test_customer_can_register_and_is_logged_in(): void
    {
        $this->post(route('register'), $this->validRegisterData())
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $user = User::where('email', 'nguyenvana@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertTrue(Hash::check('matkhau123', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_fails_when_email_already_used(): void
    {
        User::factory()->create(['email' => 'nguyenvana@gmail.com']);

        $this->post(route('register'), $this->validRegisterData())
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, User::count());
    }

    public function test_register_validates_phone_and_password_confirmation(): void
    {
        $this->post(route('register'), $this->validRegisterData([
            'phone' => '12345',
            'password_confirmation' => 'khac-nhau',
        ]))->assertSessionHasErrors(['phone', 'password']);

        $this->assertGuest();
    }

    public function test_user_cannot_register_as_admin(): void
    {
        $this->post(route('register'), $this->validRegisterData(['role' => 'admin']));

        $this->assertSame(User::ROLE_CUSTOMER, User::first()->role);
    }

    // ---------- Đăng nhập / đăng xuất (FR-02) [T-06] ----------

    public function test_login_page_is_displayed(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Ghi nhớ đăng nhập');
    }

    public function test_admin_is_redirected_to_admin_after_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_customer_returns_to_previous_page_after_login(): void
    {
        $customer = User::factory()->create();

        // Khách đang xem trang danh sách SP rồi bấm "Đăng nhập"
        $this->from(route('products.index'))->get(route('login'));

        $this->post(route('login'), ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('products.index'));
    }

    public function test_customer_returns_to_protected_page_after_login(): void
    {
        $customer = User::factory()->create();

        $this->get(route('orders.index'))->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('orders.index'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $customer = User::factory()->create();

        $this->post(route('login'), ['email' => $customer->email, 'password' => 'sai-mat-khau'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_locked_account_cannot_login(): void
    {
        $locked = User::factory()->locked()->create();

        $this->post(route('login'), ['email' => $locked->email, 'password' => 'password'])
            ->assertSessionHas('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ shop.');

        $this->assertGuest();
    }

    public function test_logged_in_user_cannot_open_login_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
