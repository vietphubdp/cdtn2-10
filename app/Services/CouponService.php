<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Coupon;

/**
 * Kiểm tra và tính tiền giảm của mã giảm giá (BR-09, FR-16).
 */
class CouponService
{
    /**
     * Tìm mã và kiểm tra còn dùng được cho đơn có tạm tính $subtotal.
     * $lock = true khi gọi trong transaction đặt hàng: khóa dòng mã để 2 đơn không cùng dùng lượt cuối.
     *
     * @throws BusinessException thông báo đúng lý do mã không dùng được
     */
    public function findUsable(string $code, int $subtotal, bool $lock = false): Coupon
    {
        $code = self::normalize($code);

        $coupon = Coupon::where('code', $code)
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->first();

        if (! $coupon) {
            throw new BusinessException('Mã giảm giá "'.$code.'" không tồn tại.');
        }

        $this->ensureUsable($coupon, $subtotal);

        return $coupon;
    }

    /**
     * BR-09: đang bật, trong thời hạn, chưa hết lượt, tạm tính ≥ đơn tối thiểu.
     *
     * @throws BusinessException
     */
    public function ensureUsable(Coupon $coupon, int $subtotal): void
    {
        $code = $coupon->code;

        if (! $coupon->is_active) {
            throw new BusinessException('Mã giảm giá "'.$code.'" đã ngừng áp dụng.');
        }
        if ($coupon->starts_at->isFuture()) {
            throw new BusinessException('Mã giảm giá "'.$code.'" chưa đến thời gian áp dụng (từ '.$coupon->starts_at->format('d/m/Y H:i').').');
        }
        if ($coupon->expires_at->isPast()) {
            throw new BusinessException('Mã giảm giá "'.$code.'" đã hết hạn.');
        }
        if ($coupon->isUsedUp()) {
            throw new BusinessException('Mã giảm giá "'.$code.'" đã hết lượt sử dụng.');
        }
        if ($subtotal < $coupon->min_order) {
            throw new BusinessException('Mã "'.$code.'" chỉ áp dụng cho đơn từ '.Coupon::money($coupon->min_order)
                .', đơn hiện tại '.Coupon::money($subtotal).'.');
        }
    }

    /**
     * Số tiền được giảm: mã % không vượt max_discount; không bao giờ lớn hơn tạm tính (total không âm).
     */
    public function discountFor(Coupon $coupon, int $subtotal): int
    {
        if ($coupon->isPercent()) {
            // Làm tròn xuống tới đồng
            $discount = intdiv($subtotal * $coupon->value, 100);

            if ($coupon->max_discount !== null) {
                $discount = min($discount, $coupon->max_discount);
            }
        } else {
            $discount = $coupon->value;
        }

        return max(0, min($discount, $subtotal));
    }

    /**
     * Mã lưu chữ in hoa, khách gõ "giam10" vẫn nhận.
     */
    public static function normalize(string $code): string
    {
        return mb_strtoupper(trim($code));
    }
}
