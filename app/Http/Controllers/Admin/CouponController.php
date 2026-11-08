<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Models\Coupon;

/**
 * Quản lý mã giảm giá (FR-16). Form thêm/sửa dùng chung view admin.coupons.form.
 */
class CouponController extends Controller
{
    public function index()
    {
        // orders_exists: mã đã dùng trong đơn thì không có nút Xóa, chỉ tắt được
        $coupons = Coupon::withExists('orders')
            ->latest('id')
            ->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        // Mặc định: bật, áp dụng từ bây giờ tới 30 ngày sau
        return view('admin.coupons.form', ['coupon' => new Coupon([
            'type' => Coupon::TYPE_PERCENT,
            'min_order' => 0,
            'is_active' => true,
            'starts_at' => now()->startOfMinute(),
            'expires_at' => now()->addDays(30)->endOfDay(),
        ])]);
    }

    public function store(CouponRequest $request)
    {
        $coupon = Coupon::create($this->data($request));

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Đã thêm mã giảm giá "'.$coupon->code.'".');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->update($this->data($request));

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Đã cập nhật mã giảm giá "'.$coupon->code.'".');
    }

    public function destroy(Coupon $coupon)
    {
        // Giữ lịch sử đơn: mã đã dùng chỉ được tắt, không xóa
        if ($coupon->orders()->exists()) {
            return back()->with('error', 'Không thể xóa mã "'.$coupon->code.'": mã đã được dùng trong đơn hàng, chỉ có thể tắt.');
        }

        $coupon->delete();

        return back()->with('success', 'Đã xóa mã giảm giá "'.$coupon->code.'".');
    }

    private function data(CouponRequest $request): array
    {
        $data = $request->safe()->except('is_active');
        $data['is_active'] = $request->boolean('is_active');
        $data['min_order'] = $data['min_order'] ?? 0;

        // Mức giảm tối đa chỉ có nghĩa với mã giảm theo %
        if ($data['type'] === Coupon::TYPE_FIXED) {
            $data['max_discount'] = null;
        }

        return $data;
    }
}
