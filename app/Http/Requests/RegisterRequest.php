<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * FR-01: email không trùng, SĐT 10 số bắt đầu bằng 0, mật khẩu ≥ 8 ký tự có xác nhận.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
