<?php

namespace App\Http\Controllers;

/**
 * Đơn hàng của tôi: lịch sử, chi tiết, hủy đơn (FR-08).
 */
class OrderController extends Controller
{
    public function index()
    {
        abort(501); // TODO [T-32]
    }

    public function show(string $code)
    {
        abort(501); // TODO [T-32]
    }

    public function cancel(string $code)
    {
        abort(501); // TODO [T-33]
    }
}
