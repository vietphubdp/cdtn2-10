<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;

/**
 * Đặt hàng COD, xử lý qua OrderService (FR-07).
 */
class CheckoutController extends Controller
{
    public function index(CartService $cart)
    {
        // BR-03: kiểm tra lại tồn kho trước khi thanh toán; giỏ bị thay đổi thì quay về giỏ để khách xem lại
        $messages = $cart->syncWithStock();
        if ($messages !== []) {
            return redirect()->route('cart.index')->with('error', implode(' ', $messages));
        }

        $items = $cart->items();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống, hãy chọn sản phẩm trước khi đặt hàng.');
        }

        return view('shop.checkout', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
            'user' => auth()->user(),
        ]);
    }

    public function store(CheckoutRequest $request, CartService $cart, OrderService $orders)
    {
        try {
            // BR-04: tồn kho được kiểm tra lần cuối trong transaction của placeOrder
            $order = $orders->placeOrder($request->user(), $request->validated(), $cart->quantities());
        } catch (BusinessException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $cart->clear();

        return redirect()->route('checkout.success', $order->code);
    }

    public function success(string $code)
    {
        $order = Order::where('code', $code)->firstOrFail();

        // Chỉ chủ đơn xem được trang này (NFR-02)
        abort_unless($order->user_id === auth()->id(), 403);

        return view('shop.success', ['order' => $order]);
    }
}
