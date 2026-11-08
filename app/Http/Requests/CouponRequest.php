<?php

namespace App\Http\Requests;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mã lưu chữ in hoa để khách gõ hoa/thường đều nhận.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => CouponService::normalize($this->input('code'))]);
        }
    }

    /**
     * FR-16: mã duy nhất; % từ 1–100; ngày hết hạn sau ngày bắt đầu.
     * Khi sửa, số lượt không được nhỏ hơn số lượt đã dùng.
     */
    public function rules(): array
    {
        /** @var Coupon|null $coupon */
        $coupon = $this->route('coupon');

        return [
            'code' => ['required', 'string', 'max:30', 'alpha_dash:ascii', Rule::unique('coupons', 'code')->ignore($coupon)],
            'type' => ['required', Rule::in([Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED])],
            'value' => ['required', 'integer', 'min:1', Rule::when($this->input('type') === Coupon::TYPE_PERCENT, 'max:100')],
            'min_order' => ['nullable', 'integer', 'min:0'],
            'max_discount' => ['nullable', 'integer', 'min:1'],
            'usage_limit' => ['nullable', 'integer', 'min:'.max(1, $coupon?->used_count ?? 0)],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'value.max' => 'Giảm theo phần trăm chỉ được từ 1 đến 100.',
            'usage_limit.min' => 'Số lượt sử dụng phải từ :min trở lên (không nhỏ hơn số lượt đã dùng).',
        ];
    }
}
