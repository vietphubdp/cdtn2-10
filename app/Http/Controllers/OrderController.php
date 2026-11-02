<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

/**
 * Đơn hàng của tôi: lịch sử, chi tiết, hủy đơn (FR-08).
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Tham số status lạ thì bỏ qua, hiện tất cả
        $status = OrderStatus::tryFrom((string) $request->query('status'));

        $orders = $request->user()->orders()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('account.orders.index', [
            'orders' => $orders,
            'currentStatus' => $status,
        ]);
    }

    public function show(string $code)
    {
        $order = $this->findOwnOrder($code);
        $order->load('items', 'coupon');

        return view('account.orders.show', ['order' => $order]);
    }

    public function cancel(string $code, OrderService $orders)
    {
        $order = $this->findOwnOrder($code);

        // BR-05: khách chỉ được hủy khi đơn còn "Chờ xác nhận"
        if (! $order->status->canBeCancelledByCustomer()) {
            return back()->with('error', 'Đơn '.$order->code.' đang ở trạng thái "'.$order->status->label().'", không thể hủy. Vui lòng liên hệ shop.');
        }

        try {
            // BR-06: hủy đơn cộng lại tồn kho, xử lý trong OrderService
            $orders->cancel($order);
        } catch (BusinessException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order->code)->with('success', 'Đã hủy đơn '.$order->code.'.');
    }

    /**
     * Tìm đơn theo mã; đơn của người khác → 403 (NFR-02).
     */
    private function findOwnOrder(string $code): Order
    {
        $order = Order::where('code', $code)->firstOrFail();

        abort_unless($order->user_id === auth()->id(), 403);

        return $order;
    }
}
