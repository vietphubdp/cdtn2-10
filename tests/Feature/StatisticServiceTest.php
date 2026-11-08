<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\StatisticService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StatisticServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatisticService $statistics;

    protected function setUp(): void
    {
        parent::setUp();

        // Cố định "hôm nay" là giữa tháng để không vướng ranh giới tháng/năm
        $this->travelTo(Carbon::parse('2026-11-15 10:00:00'));
        $this->statistics = new StatisticService;
    }

    private function order(OrderStatus $status, int $total, string $createdAt): Order
    {
        $order = Order::factory()->status($status)->create(['subtotal' => $total, 'total' => $total]);
        $order->created_at = Carbon::parse($createdAt);
        $order->save();

        return $order;
    }

    public function test_revenue_only_counts_completed_orders(): void
    {
        $this->order(OrderStatus::Completed, 500000, '2026-11-15 08:00:00');
        $this->order(OrderStatus::Completed, 300000, '2026-11-02 09:00:00');
        // Các đơn chưa hoàn thành hoặc đã hủy không được tính (BR-08)
        $this->order(OrderStatus::Pending, 1000000, '2026-11-15 09:00:00');
        $this->order(OrderStatus::Confirmed, 2000000, '2026-11-15 09:00:00');
        $this->order(OrderStatus::Shipping, 3000000, '2026-11-14 09:00:00');
        $this->order(OrderStatus::Cancelled, 4000000, '2026-11-15 09:00:00');

        $this->assertSame(500000, $this->statistics->revenueToday());
        $this->assertSame(800000, $this->statistics->revenueThisMonth());
        $this->assertSame(800000, array_sum($this->statistics->revenueByMonth(2026)));
        $this->assertSame(500000, array_sum($this->statistics->revenueByDay(7)));
    }

    public function test_today_follows_vietnam_time(): void
    {
        // 23:30 ngày 14 giờ UTC = 06:30 sáng ngày 15 giờ Việt Nam
        $this->travelTo(Carbon::parse('2026-11-14 23:30:00', 'UTC'));
        Order::factory()->status(OrderStatus::Completed)->create(['subtotal' => 250000, 'total' => 250000]);

        $this->assertSame('2026-11-15 06:30', Order::first()->created_at->format('Y-m-d H:i'));
        $this->assertSame(250000, $this->statistics->revenueToday());
        $this->assertSame(['2026-11-15' => 250000], array_slice($this->statistics->revenueByDay(7), -1, 1, true));
    }

    public function test_revenue_is_zero_when_there_are_no_orders(): void
    {
        $this->assertSame(0, $this->statistics->revenueToday());
        $this->assertSame(0, $this->statistics->revenueThisMonth());
    }

    public function test_revenue_today_and_this_month_use_created_at(): void
    {
        $this->order(OrderStatus::Completed, 100000, '2026-11-15 00:00:00'); // đầu ngày hôm nay
        $this->order(OrderStatus::Completed, 200000, '2026-11-14 23:59:59'); // hôm qua
        $this->order(OrderStatus::Completed, 400000, '2026-11-01 00:00:00'); // đầu tháng
        $this->order(OrderStatus::Completed, 800000, '2026-10-31 23:59:59'); // tháng trước

        $this->assertSame(100000, $this->statistics->revenueToday());
        $this->assertSame(700000, $this->statistics->revenueThisMonth());
    }

    public function test_revenue_by_day_fills_missing_days_with_zero(): void
    {
        $this->order(OrderStatus::Completed, 100000, '2026-11-15 07:00:00');
        $this->order(OrderStatus::Completed, 150000, '2026-11-15 20:00:00');
        $this->order(OrderStatus::Completed, 200000, '2026-11-12 12:00:00');
        $this->order(OrderStatus::Completed, 900000, '2026-11-08 12:00:00'); // ngoài 7 ngày

        $this->assertSame([
            '2026-11-09' => 0,
            '2026-11-10' => 0,
            '2026-11-11' => 0,
            '2026-11-12' => 200000,
            '2026-11-13' => 0,
            '2026-11-14' => 0,
            '2026-11-15' => 250000,
        ], $this->statistics->revenueByDay());

        $this->assertCount(30, $this->statistics->revenueByDay(30));
        $this->assertSame(1350000, array_sum($this->statistics->revenueByDay(30)));
    }

    public function test_revenue_by_month_returns_all_twelve_months_of_the_year(): void
    {
        $this->order(OrderStatus::Completed, 100000, '2026-01-01 00:00:00');
        $this->order(OrderStatus::Completed, 200000, '2026-11-03 10:00:00');
        $this->order(OrderStatus::Completed, 300000, '2026-11-15 10:00:00');
        $this->order(OrderStatus::Completed, 400000, '2026-12-31 23:59:59');
        $this->order(OrderStatus::Completed, 999000, '2025-12-31 23:59:59'); // năm trước

        $months = $this->statistics->revenueByMonth(2026);

        $this->assertSame(range(1, 12), array_keys($months));
        $this->assertSame(100000, $months[1]);
        $this->assertSame(0, $months[2]);
        $this->assertSame(500000, $months[11]);
        $this->assertSame(400000, $months[12]);
        $this->assertSame(999000, $this->statistics->revenueByMonth(2025)[12]);
    }

    public function test_order_count_by_status_includes_every_status(): void
    {
        Order::factory()->count(3)->status(OrderStatus::Pending)->create();
        Order::factory()->status(OrderStatus::Completed)->create();
        Order::factory()->count(2)->status(OrderStatus::Cancelled)->create();

        $this->assertSame([
            'pending' => 3,
            'confirmed' => 0,
            'shipping' => 0,
            'completed' => 1,
            'cancelled' => 2,
        ], $this->statistics->orderCountByStatus());
    }

    public function test_top_products_sums_quantity_of_completed_orders_only(): void
    {
        $mouse = Product::factory()->create(['name' => 'Chuột A']);
        $keyboard = Product::factory()->create(['name' => 'Bàn phím B']);
        $speaker = Product::factory()->create(['name' => 'Loa C']);

        $done1 = Order::factory()->status(OrderStatus::Completed)->create();
        $done2 = Order::factory()->status(OrderStatus::Completed)->create();
        $pending = Order::factory()->status(OrderStatus::Pending)->create();

        $item = fn (Order $order, Product $product, int $quantity, int $price) => OrderItem::factory()->create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'price' => $price, 'quantity' => $quantity, 'line_total' => $price * $quantity,
        ]);

        $item($done1, $mouse, 2, 300000);
        $item($done2, $mouse, 1, 250000); // giá lúc mua khác nhau vẫn cộng đúng line_total
        $item($done1, $keyboard, 5, 100000);
        $item($done2, $speaker, 1, 900000);
        $item($pending, $speaker, 10, 900000); // đơn chưa hoàn thành không tính

        $this->assertSame([
            ['product_id' => $keyboard->id, 'name' => 'Bàn phím B', 'quantity' => 5, 'revenue' => 500000],
            ['product_id' => $mouse->id, 'name' => 'Chuột A', 'quantity' => 3, 'revenue' => 850000],
            ['product_id' => $speaker->id, 'name' => 'Loa C', 'quantity' => 1, 'revenue' => 900000],
        ], $this->statistics->topProducts());

        $this->assertCount(2, $this->statistics->topProducts(2));
    }

    public function test_top_products_is_empty_without_completed_orders(): void
    {
        OrderItem::factory()->create(); // thuộc đơn pending

        $this->assertSame([], $this->statistics->topProducts());
    }
}
