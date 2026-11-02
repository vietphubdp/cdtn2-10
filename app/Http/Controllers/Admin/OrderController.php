<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Quản lý đơn hàng, đổi trạng thái theo OrderStatus (FR-11).
 */
class OrderController extends Controller
{
    /**
     * Danh sách đơn (Hình 15): lọc theo trạng thái, mã đơn, khoảng ngày đặt;
     * đếm số đơn theo từng trạng thái; đơn mới nhất lên đầu, 20 đơn/trang.
     */
    public function index(Request $request)
    {
        // Tham số sai kiểu hoặc giá trị lạ coi như không lọc
        $status = OrderStatus::tryFrom((string) $request->query('status'));
        $code = is_string($request->query('code')) ? trim($request->query('code')) : '';
        $from = $this->dateParam($request, 'from');
        $to = $this->dateParam($request, 'to');

        // Lọc mã đơn và ngày đặt áp dụng cho cả bảng lẫn số đếm trên các tab trạng thái
        $filtered = Order::query()
            ->when($code !== '', fn ($query) => $query->where('code', 'like', '%'.$code.'%'))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to));

        // [giá trị trạng thái => số đơn]
        $counts = (clone $filtered)->toBase()
            ->selectRaw('status, count(*) as order_count')
            ->groupBy('status')
            ->pluck('order_count', 'status');

        $orders = $filtered
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'counts', 'status', 'code', 'from', 'to'));
    }

    public function show(Order $order)
    {
        abort(501); // TODO [T-36]
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort(501); // TODO [T-36]
    }

    /**
     * Ngày dạng Y-m-d từ ô chọn ngày; sai định dạng thì bỏ qua.
     */
    private function dateParam(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts)) {
            return null;
        }

        return checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1]) ? $value : null;
    }
}
