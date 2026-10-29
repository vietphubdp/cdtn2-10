<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PerformanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_admin_and_two_customers(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@techshop.test')->first();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('password', $admin->password));

        $this->assertSame(2, User::where('role', User::ROLE_CUSTOMER)->count());
        $this->assertDatabaseHas('users', ['email' => 'khach@techshop.test', 'role' => User::ROLE_CUSTOMER]);
    }

    public function test_seeder_creates_categories_and_products_for_demo(): void
    {
        $this->seed();

        $this->assertSame(6, Category::count());
        $this->assertSame(60, Product::count());
        $this->assertDatabaseHas('categories', ['slug' => 'tai-nghe']);

        // Đủ trường hợp để demo: có KM, hết hàng, bị ẩn
        $this->assertGreaterThan(0, Product::whereNotNull('sale_price')->count());
        $this->assertGreaterThan(0, Product::where('stock', 0)->count());
        $this->assertGreaterThan(0, Product::where('is_active', false)->count());
    }

    public function test_seeded_sale_price_is_lower_than_price(): void
    {
        $this->seed();

        $this->assertSame(0, Product::whereNotNull('sale_price')->whereColumn('sale_price', '>=', 'price')->count());
    }

    public function test_performance_seeder_adds_500_products(): void
    {
        $this->seed(PerformanceSeeder::class);

        $this->assertSame(500, Product::count());
        $this->assertSame(6, Category::count());
    }
}
