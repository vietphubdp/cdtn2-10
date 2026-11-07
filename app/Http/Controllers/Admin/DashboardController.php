<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StatisticService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard thống kê (FR-13). Mọi số liệu doanh thu lấy từ StatisticService,
     * chỉ tính đơn "Hoàn thành" theo ngày đặt (BR-08).
     */
    public function index(Request $request, StatisticService $statistics)
    {
        $statusCounts = $statistics->orderCountByStatus();

        // Biểu đồ: mặc định 7 ngày gần nhất, ?range=month để xem 12 tháng năm nay
        $range = $request->query('range') === 'month' ? 'month' : 'day';

        $chart = [];
        if ($range === 'month') {
            foreach ($statistics->revenueByMonth(now()->year) as $month => $total) {
                $chart['T'.$month] = $total;
            }
        } else {
            foreach ($statistics->revenueByDay(7) as $date => $total) {
                $chart[Carbon::parse($date)->format('d/m')] = $total;
            }
        }

        return view('admin.dashboard', [
            'revenueToday' => $statistics->revenueToday(),
            'revenueThisMonth' => $statistics->revenueThisMonth(),
            'pendingCount' => $statusCounts[OrderStatus::Pending->value],
            'customerCount' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'statusCounts' => $statusCounts,
            'range' => $range,
            'chart' => $chart,
            'topProducts' => $statistics->topProducts(5),
        ]);
    }
}
