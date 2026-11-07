<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->put(route('profile.update'), ['name' => 'A'])->assertRedirect(route('login'));
        $this->put(route('profile.password'), [])->assertRedirect(route('login'));
    }

    public function test_profile_page_shows_user_info(): void
    {
        $user = User::factory()->create(['name' => 'Nguyễn Văn A', 'email' => 'a@example.com']);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertViewIs('account.profile')
            ->assertSee('Nguyễn Văn A')
            ->assertSee('a@example.com')
            ->assertSee('id="doi-mat-khau"', false);
    }

    public function test_customer_can_update_profile(): void
    {
        $user = User::factory()->create(['email' => 'a@example.com']);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Trần Thị B',
            'phone' => '0912345678',
            'address' => '12 Nguyễn Huệ, Quận 1, TP.HCM',
            'email' => 'doi@example.com',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Trần Thị B', $user->name);
        $this->assertSame('0912345678', $user->phone);
        $this->assertSame('12 Nguyễn Huệ, Quận 1, TP.HCM', $user->address);
        // Email không được sửa qua form hồ sơ
        $this->assertSame('a@example.com', $user->email);
    }

    public function test_checkout_form_uses_updated_profile(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Trần Thị B',
            'phone' => '0912345678',
            'address' => '12 Nguyễn Huệ, Quận 1',
        ]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Trần Thị B')
            ->assertSee('0912345678')
            ->assertSee('12 Nguyễn Huệ, Quận 1');
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $user = User::factory()->create(['phone' => '0900000000']);

        $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => 'Trần Thị B',
            'phone' => '12345',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrors('phone');

        $this->assertSame('0900000000', $user->fresh()->phone);
    }

    public function test_name_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => '',
            'phone' => '0912345678',
        ])->assertSessionHasErrors('name');
    }

    public function test_customer_can_change_password_and_login_with_new_one(): void
    {
        $user = User::factory()->create(['email' => 'a@example.com', 'password' => Hash::make('old-password')]);

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('profile.edit').'#doi-mat-khau')->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));

        $this->post(route('logout'));
        $this->assertGuest();

        $this->post(route('login'), ['email' => 'a@example.com', 'password' => 'old-password']);
        $this->assertGuest();

        $this->post(route('login'), ['email' => 'a@example.com', 'password' => 'new-password']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'sai-mat-khau',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_new_password_must_be_confirmed_and_long_enough(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'old-password',
            'password' => 'short',
            'password_confirmation' => 'khac',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
