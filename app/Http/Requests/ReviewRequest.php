<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * FR-12: chấm 1–5 sao, nhận xét không bắt buộc.
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Nhập sai thì quay lại đúng khung đánh giá (không nhảy lên đầu trang).
     */
    protected function getRedirectUrl()
    {
        return url()->previous().'#danh-gia';
    }
}
