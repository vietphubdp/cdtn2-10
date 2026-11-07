<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PerformanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_admin_and_three_customers_with_one_locked(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@techshop.test')->first();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('password', $admin->password));

        $this->assertSame(3, User::where('role', User::ROLE_CUSTOMER)->count());
        $this->assertDatabaseHas('users', ['email' => 'khach@techshop.test', 'role' => User::ROLE_CUSTOMER, 'is_locked' => false]);
        $this->assertDatabaseHas('users', ['email' => 'locked@techshop.test', 'role' => User::ROLE_CUSTOMER, 'is_locked' => true]);
        $this->assertSame(1, User::where('is_locked', true)->count());
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

    public function test_seeder_creates_orders_in_all_five_statuses_for_two_customers(): void
    {
        $this->seed();

        $this->assertSame(12, Order::count());
        foreach (OrderStatus::cases() as $status) {
            $this->assertGreaterThan(0, Order::where('status', $status)->count(), $status->label());
        }

        $customerIds = User::whereIn('email', ['khach@techshop.test', 'khach2@techshop.test'])->pluck('id');
        $this->assertSame(0, Order::whereNotIn('user_id', $customerIds)->count());
        $this->assertSame(2, Order::distinct()->count('user_id'));

        // Mã đơn TS-xxxx, tổng tiền khớp các dòng đơn
        $order = Order::with('items')->first();
        $this->assertSame(Order::makeCode($order->id), $order->code);
        $this->assertSame($order->items->sum('line_total'), $order->total);
    }

    public function test_seeded_orders_reduce_stock_except_cancelled_orders(): void
    {
        $this->seed();

        // Đơn đã giao xong trừ kho: Sony WH-1000XM5 từ 15 còn 14
        $this->assertSame(14, Product::where('name', 'Tai nghe Sony WH-1000XM5')->value('stock'));
        // Đơn bị hủy được cộng lại kho: JBL Go 4 vẫn 40
        $this->assertSame(40, Product::where('name', 'Loa bluetooth JBL Go 4')->value('stock'));
    }

    public function test_performance_seeder_adds_500_products(): void
    {
        $this->seed(PerformanceSeeder::class);

        $this->assertSame(500, Product::count());
        $this->assertSame(6, Category::count());
    }
}
