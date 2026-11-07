<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * FR-15: sửa họ tên, SĐT, địa chỉ. Email chỉ hiển thị nên không nhận ở đây.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
