<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Số liệu cho dashboard quản trị (FR-13).
 * BR-08: doanh thu chỉ tính đơn "Hoàn thành", theo ngày đặt (created_at).
 * Việc gom theo ngày/tháng làm bằng PHP để chạy giống nhau trên MySQL và SQLite.
 */
class StatisticService
{
    public function revenueToday(): int
    {
        return $this->revenueBetween(today(), today()->endOfDay());
    }

    public function revenueThisMonth(): int
    {
        return $this->revenueBetween(now()->startOfMonth(), now()->endOfMonth());
    }

    /**
     * Doanh thu từng ngày của $days ngày gần nhất (tính cả hôm nay), cũ trước mới sau.
     *
     * @return array<string, int> ['Y-m-d' => tổng tiền], ngày không có đơn = 0
     */
    public function revenueByDay(int $days = 7): array
    {
        $from = today()->subDays($days - 1);

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $result[$from->copy()->addDays($i)->format('Y-m-d')] = 0;
        }

        foreach ($this->completedOrdersBetween($from, today()->endOfDay()) as $order) {
            $result[$order->created_at->format('Y-m-d')] += $order->total;
        }

        return $result;
    }

    /**
     * Doanh thu 12 tháng của một năm.
     *
     * @return array<int, int> [tháng 1..12 => tổng tiền], tháng không có đơn = 0
     */
    public function revenueByMonth(int $year): array
    {
        $result = array_fill(1, 12, 0);

        $start = Carbon::create($year)->startOfYear();
        foreach ($this->completedOrdersBetween($start, $start->copy()->endOfYear()) as $order) {
            $result[$order->created_at->month] += $order->total;
        }

        return $result;
    }

    /**
     * Số đơn theo từng trạng thái (tất cả đơn, không chỉ đơn hoàn thành).
     *
     * @return array<string, int> ['pending' => 3, ...] đủ 5 trạng thái, kể cả 0
     */
    public function orderCountByStatus(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->toBase()
            ->pluck('aggregate', 'status');

        $result = [];
        foreach (OrderStatus::cases() as $status) {
            $result[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        return $result;
    }

    /**
     * Sản phẩm bán chạy nhất theo tổng số lượng trong các đơn hoàn thành.
     *
     * @return array<int, array{product_id: int, name: string, quantity: int, revenue: int}>
     */
    public function topProducts(int $limit = 5): array
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->groupBy('order_items.product_id', 'products.name')
            ->selectRaw('order_items.product_id, products.name, SUM(order_items.quantity) as quantity, SUM(order_items.line_total) as revenue')
            ->orderByDesc('quantity')
            ->orderByDesc('revenue')
            ->orderBy('order_items.product_id')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'name' => $row->name,
                'quantity' => (int) $row->quantity,
                'revenue' => (int) $row->revenue,
            ])
            ->all();
    }

    private function revenueBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        return (int) $this->completedQuery($from, $to)->sum('total');
    }

    /**
     * @return iterable<Order>
     */
    private function completedOrdersBetween(CarbonInterface $from, CarbonInterface $to): iterable
    {
        return $this->completedQuery($from, $to)->get(['id', 'total', 'created_at']);
    }

    /**
     * @return Builder<Order>
     */
    private function completedQuery(CarbonInterface $from, CarbonInterface $to): Builder
    {
        return Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereBetween('created_at', [$from, $to]);
    }
}
