<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cố định "hôm nay" giữa tháng để không vướng ranh giới tháng/năm
        $this->travelTo(Carbon::parse('2026-11-15 10:00:00'));
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function order(OrderStatus $status, int $total, string $createdAt): Order
    {
        $order = Order::factory()->status($status)->create(['subtotal' => $total, 'total' => $total]);
        $order->created_at = Carbon::parse($createdAt);
        $order->save();

        return $order;
    }

    public function test_guest_is_redirected_and_customer_is_forbidden(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_revenue_cards_only_count_completed_orders(): void
    {
        // TC-27: đơn chờ xác nhận / đã hủy không được tính vào doanh thu (BR-08)
        $this->order(OrderStatus::Completed, 1000000, '2026-11-15 08:00:00');
        $this->order(OrderStatus::Completed, 500000, '2026-11-03 09:00:00');
        $this->order(OrderStatus::Pending, 300000, '2026-11-15 09:00:00');
        $this->order(OrderStatus::Cancelled, 200000, '2026-11-15 09:00:00');
        $this->order(OrderStatus::Completed, 700000, '2026-10-20 09:00:00'); // tháng trước

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('revenueToday', 1000000)
            ->assertViewHas('revenueThisMonth', 1500000)
            ->assertSeeInOrder(['Doanh thu hôm nay', '1.000.000đ', 'Doanh thu tháng này', '1.500.000đ']);
    }

    public function test_dashboard_shows_order_count_by_status_with_links(): void
    {
        $this->order(OrderStatus::Pending, 100000, '2026-11-15 08:00:00');
        $this->order(OrderStatus::Pending, 100000, '2026-11-14 08:00:00');
        $this->order(OrderStatus::Shipping, 100000, '2026-11-14 08:00:00');

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'));

        $response->assertViewHas('pendingCount', 2)
            ->assertViewHas('statusCounts', [
                'pending' => 2, 'confirmed' => 0, 'shipping' => 1, 'completed' => 0, 'cancelled' => 0,
            ])
            ->assertSee('Đơn chờ xác nhận');

        foreach (OrderStatus::cases() as $status) {
            $response->assertSee(route('admin.orders.index', ['status' => $status->value]), false)
                ->assertSee($status->label());
        }
    }

    public function test_chart_shows_last_seven_days_by_default(): void
    {
        $this->order(OrderStatus::Completed, 400000, '2026-11-15 08:00:00');
        $this->order(OrderStatus::Completed, 250000, '2026-11-10 08:00:00');
        $this->order(OrderStatus::Completed, 900000, '2026-11-01 08:00:00'); // ngoài 7 ngày

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'));

        $response->assertViewHas('range', 'day')
            ->assertViewHas('chart', [
                '09/11' => 0, '10/11' => 250000, '11/11' => 0, '12/11' => 0,
                '13/11' => 0, '14/11' => 0, '15/11' => 400000,
            ])
            ->assertSee('Doanh thu 7 ngày gần nhất')
            ->assertSee('15/11: 400.000đ')
            ->assertSee('height: 100%', false) // cột cao nhất
            ->assertSee('height: 63%', false); // 250.000 / 400.000
    }

    public function test_chart_can_switch_to_twelve_months(): void
    {
        $this->order(OrderStatus::Completed, 400000, '2026-11-15 08:00:00');
        $this->order(OrderStatus::Completed, 600000, '2026-03-02 08:00:00');
        $this->order(OrderStatus::Completed, 900000, '2025-12-31 08:00:00'); // năm trước

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard', ['range' => 'month']));

        $response->assertViewHas('range', 'month')
            ->assertSee('Doanh thu 12 tháng năm 2026');

        $chart = $response->viewData('chart');
        $this->assertCount(12, $chart);
        $this->assertSame(600000, $chart['T3']);
        $this->assertSame(400000, $chart['T11']);
        $this->assertSame(1000000, array_sum($chart));
    }

    public function test_unknown_range_falls_back_to_days(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard', ['range' => 'abc']))
            ->assertOk()
            ->assertViewHas('range', 'day');
    }

    public function test_top_products_are_sorted_by_quantity_sold(): void
    {
        $mouse = Product::factory()->create(['name' => 'Chuột A']);
        $keyboard = Product::factory()->create(['name' => 'Bàn phím B']);
        $speaker = Product::factory()->create(['name' => 'Loa C']);

        $done = Order::factory()->status(OrderStatus::Completed)->create();
        $pending = Order::factory()->status(OrderStatus::Pending)->create();

        $item = fn (Order $order, Product $product, int $quantity, int $price) => OrderItem::factory()->create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'price' => $price, 'quantity' => $quantity, 'line_total' => $price * $quantity,
        ]);

        $item($done, $mouse, 2, 300000);
        $item($done, $keyboard, 5, 100000);
        $item($done, $speaker, 1, 900000);
        $item($pending, $speaker, 10, 900000); // đơn chưa hoàn thành không tính

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertSee('Top 5 bán chạy')
            ->assertSeeInOrder([
                '1. Bàn phím B', '5', '500.000đ',
                '2. Chuột A', '2', '600.000đ',
                '3. Loa C', '1', '900.000đ',
            ]);
    }

    public function test_empty_dashboard_shows_zero_and_no_top_products(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('revenueToday', 0)
            ->assertSee('0đ')
            ->assertSee('Chưa có đơn hàng hoàn thành.');
    }

    public function test_seeded_data_has_revenue_today(): void
    {
        $this->travelBack();
        $this->seed();

        // Đơn hoàn thành hôm nay trong dữ liệu mẫu: củ sạc Anker 590.000 + cáp Anker 390.000
        $this->actingAs(User::where('email', 'admin@techshop.test')->firstOrFail())
            ->get(route('admin.dashboard'))
            ->assertViewHas('revenueToday', 980000)
            ->assertViewHas('pendingCount', 2)
            ->assertViewHas('customerCount', 3);
    }
}
