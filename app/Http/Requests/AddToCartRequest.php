<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Không gửi số lượng thì thêm 1. SP bị ẩn/hết hàng và tổng số lượng vượt tồn kho
     * do CartService kiểm tra để báo lỗi rõ cho khách (BR-02, BR-03).
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
