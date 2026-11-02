<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * FR-11: chỉ nhận giá trị có trong OrderStatus; chuyển có hợp lệ hay không (BR-05) do OrderService kiểm tra.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái mới.',
            'status.enum' => 'Trạng thái không hợp lệ.',
        ];
    }
}
