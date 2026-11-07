<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create(['name' => 'Quản trị viên']);
    }

    public function test_admin_sees_user_list_with_order_count(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create(['name' => 'Nguyễn Văn An']);
        Order::factory()->count(2)->create(['user_id' => $customer->id]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertViewIs('admin.users.index')
            ->assertSee('Nguyễn Văn An')
            ->assertSee($customer->email)
            ->assertSee('Quản trị viên')
            ->assertViewHas('users', fn ($users) => $users->firstWhere('id', $customer->id)->orders_count === 2);
    }

    public function test_admin_can_search_by_name_email_or_phone(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Nguyễn Văn An', 'email' => 'an@example.com', 'phone' => '0911111111']);
        User::factory()->create(['name' => 'Trần Thị Bình', 'email' => 'binh@example.com', 'phone' => '0922222222']);

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'Văn An']))
            ->assertSee('an@example.com')->assertDontSee('binh@example.com');

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'binh@']))
            ->assertSee('binh@example.com')->assertDontSee('an@example.com');

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => '0922']))
            ->assertSee('binh@example.com')->assertDontSee('an@example.com');
    }

    public function test_admin_can_filter_by_status_together_with_search(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Khách Hoạt Động', 'email' => 'active@example.com']);
        User::factory()->locked()->create(['name' => 'Khách Bị Khóa', 'email' => 'locked@example.com']);

        $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'locked']))
            ->assertSee('locked@example.com')->assertDontSee('active@example.com');

        $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'active']))
            ->assertSee('active@example.com')->assertDontSee('locked@example.com');

        // Tìm "Khách" khớp cả 2 người, lọc thêm trạng thái chỉ còn 1
        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'Khách', 'status' => 'locked']))
            ->assertSee('locked@example.com')->assertDontSee('active@example.com');
    }

    public function test_invalid_filter_values_are_ignored(): void
    {
        $admin = $this->admin();
        User::factory()->locked()->create(['email' => 'locked@example.com']);

        $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'abc', 'q' => ['x']]))
            ->assertOk()
            ->assertSee('locked@example.com');
    }

    public function test_pagination_keeps_query_string(): void
    {
        $admin = $this->admin();
        User::factory()->count(25)->create(['name' => 'Khách mẫu']);

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'Khách mẫu']))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->count() === 20 && $users->total() === 25)
            ->assertSee('q=Kh%C3%A1ch%20m%E1%BA%ABu&amp;page=2', false);
    }

    public function test_admin_can_lock_customer_and_customer_cannot_login(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create(['name' => 'Trần Thị Bình']);

        $this->actingAs($admin)
            ->from(route('admin.users.index', ['q' => 'Bình']))
            ->patch(route('admin.users.lock', $customer))
            ->assertRedirect(route('admin.users.index', ['q' => 'Bình']))
            ->assertSessionHas('success', 'Đã khóa tài khoản "Trần Thị Bình". Người này không thể đăng nhập nữa.');

        $this->assertTrue($customer->fresh()->is_locked);

        // TC-28: khách vừa bị khóa đăng nhập bị từ chối
        $this->post(route('logout'));
        $this->post(route('login'), ['email' => $customer->email, 'password' => 'password'])
            ->assertSessionHas('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ shop.');
        $this->assertGuest();
    }

    public function test_locked_customer_who_is_logged_in_is_logged_out(): void
    {
        $customer = User::factory()->create();

        // Khách đang đăng nhập thì bị admin khóa
        $this->actingAs($customer);
        $customer->is_locked = true;
        $customer->save();

        $this->get(route('orders.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Tài khoản của bạn đã bị khóa.');
        $this->assertGuest();
    }

    public function test_admin_can_unlock_customer_and_customer_can_login_again(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->locked()->create(['name' => 'Lê Văn Cường']);

        $this->actingAs($admin)
            ->patch(route('admin.users.lock', $customer))
            ->assertSessionHas('success', 'Đã mở khóa tài khoản "Lê Văn Cường".');

        $this->assertFalse($customer->fresh()->is_locked);

        $this->post(route('logout'));
        $this->post(route('login'), ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect();
        $this->assertAuthenticatedAs($customer->fresh());
    }

    public function test_admin_account_cannot_be_locked(): void
    {
        $admin = $this->admin();
        $otherAdmin = User::factory()->admin()->create(['name' => 'Admin Hai']);

        $this->actingAs($admin)
            ->patch(route('admin.users.lock', $otherAdmin))
            ->assertSessionHas('error', 'Không thể khóa tài khoản quản trị "Admin Hai".');

        $this->actingAs($admin)
            ->patch(route('admin.users.lock', $admin))
            ->assertSessionHas('error');

        $this->assertFalse($otherAdmin->fresh()->is_locked);
        $this->assertFalse($admin->fresh()->is_locked);
    }

    public function test_lock_button_not_shown_for_admin_rows(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertSee(route('admin.users.lock', $customer))
            ->assertDontSee(route('admin.users.lock', $admin));
    }

    public function test_customer_cannot_access_user_management(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.users.lock', $other))->assertForbidden();

        $this->assertFalse($other->fresh()->is_locked);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }
}
