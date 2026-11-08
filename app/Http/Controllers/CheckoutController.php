<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderPlacedMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Đặt hàng COD, xử lý qua OrderService (FR-07); áp mã giảm giá qua CouponService (FR-16);
 * gửi email xác nhận đơn (FR-18).
 * Mã khách đã áp lưu trong session key "coupon" (chỉ lưu chuỗi mã, mỗi lần dùng đều kiểm tra lại).
 */
class CheckoutController extends Controller
{
    private const COUPON_KEY = 'coupon';

    public function __construct(private CouponService $coupons) {}

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

        $subtotal = $items->sum('line_total');

        // Giỏ đổi sau khi áp mã (vd. bớt SP, tạm tính dưới mức tối thiểu) → mã không còn hợp lệ thì bỏ và báo khách
        $coupon = null;
        try {
            $coupon = $this->appliedCoupon($subtotal);
        } catch (BusinessException $e) {
            session()->forget(self::COUPON_KEY);
            session()->now('error', $e->getMessage().' Mã đã được bỏ khỏi đơn hàng.');
        }
        $discount = $coupon ? $this->coupons->discountFor($coupon, $subtotal) : 0;

        return view('shop.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'user' => auth()->user(),
        ]);
    }

    public function store(CheckoutRequest $request, CartService $cart, OrderService $orders)
    {
        // Mã hết hạn/hết lượt trong lúc khách điền form → quay lại trang thanh toán, không đặt đơn
        try {
            $coupon = $this->appliedCoupon($cart->subtotal());
        } catch (BusinessException $e) {
            session()->forget(self::COUPON_KEY);

            return redirect()->route('checkout.index')->withInput()
                ->with('error', $e->getMessage().' Mã đã được bỏ, vui lòng kiểm tra lại tổng tiền rồi đặt hàng.');
        }

        try {
            // BR-04: tồn kho và mã giảm giá được kiểm tra lần cuối trong transaction của placeOrder
            $order = $orders->placeOrder($request->user(), $request->validated(), $cart->quantities(), $coupon?->code);
        } catch (BusinessException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $cart->clear();
        session()->forget(self::COUPON_KEY);

        // Đơn đã lưu xong: gửi mail lỗi (sai cấu hình, mất mạng…) chỉ ghi log, không làm hỏng đơn
        try {
            Mail::to($request->user())->send(new OrderPlacedMail($order));
        } catch (Throwable $e) {
            report($e);
        }

        return redirect()->route('checkout.success', $order->code);
    }

    public function applyCoupon(ApplyCouponRequest $request, CartService $cart)
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống, hãy chọn sản phẩm trước khi đặt hàng.');
        }

        $subtotal = $cart->subtotal();

        try {
            $coupon = $this->coupons->findUsable($request->validated('code'), $subtotal);
        } catch (BusinessException $e) {
            // Lỗi hiện ngay dưới ô nhập mã
            return redirect()->route('checkout.index')
                ->withErrors(['code' => $e->getMessage()])
                ->withInput($request->only('code'));
        }

        session()->put(self::COUPON_KEY, $coupon->code);

        return redirect()->route('checkout.index')->with('success', 'Đã áp dụng mã "'.$coupon->code.'": giảm '
            .Coupon::money($this->coupons->discountFor($coupon, $subtotal)).'.');
    }

    public function removeCoupon()
    {
        session()->forget(self::COUPON_KEY);

        return redirect()->route('checkout.index')->with('success', 'Đã bỏ mã giảm giá.');
    }

    public function success(string $code)
    {
        $order = Order::where('code', $code)->firstOrFail();

        // Chỉ chủ đơn xem được trang này (NFR-02)
        abort_unless($order->user_id === auth()->id(), 403);

        return view('shop.success', ['order' => $order]);
    }

    /**
     * Mã khách đã áp (nếu có), kiểm tra lại với tạm tính hiện tại.
     *
     * @throws BusinessException
     */
    private function appliedCoupon(int $subtotal): ?Coupon
    {
        $code = session(self::COUPON_KEY);

        return is_string($code) ? $this->coupons->findUsable($code, $subtotal) : null;
    }
}
