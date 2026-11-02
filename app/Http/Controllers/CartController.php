<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\UpdateCartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

/**
 * Giỏ hàng lưu trong session, mọi xử lý đi qua CartService (FR-06).
 */
class CartController extends Controller
{
    public function index(CartService $cart)
    {
        // BR-03: mỗi lần mở giỏ kiểm tra lại tồn kho, giảm số lượng vượt kho và báo cho khách
        $messages = $cart->syncWithStock();
        if ($messages !== []) {
            session()->now('error', implode(' ', $messages));
        }

        $items = $cart->items();

        return view('shop.cart', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
        ]);
    }

    public function add(Request $request)
    {
        abort(501); // TODO [T-25]
    }

    public function update(UpdateCartRequest $request, int $productId, CartService $cart)
    {
        try {
            $cart->update($productId, $request->integer('quantity'));
        } catch (BusinessException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->route('cart.index')->with('success', 'Đã cập nhật số lượng.');
    }

    public function remove(int $productId, CartService $cart)
    {
        $cart->remove($productId);

        return redirect()->route('cart.index')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }
}
