<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_customer_gets_403_on_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_passes_role_middleware(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get('/admin');

        $this->assertNotContains($response->status(), [302, 403]);
    }

    public function test_locked_user_is_logged_out(): void
    {
        $this->actingAs(User::factory()->locked()->create())
            ->get(route('orders.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_locked_admin_cannot_open_admin(): void
    {
        $this->actingAs(User::factory()->admin()->locked()->create())
            ->get('/admin')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
