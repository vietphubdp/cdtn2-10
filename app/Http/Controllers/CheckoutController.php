<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Đặt hàng COD, xử lý qua OrderService (FR-07).
 */
class CheckoutController extends Controller
{
    public function index()
    {
        abort(501); // TODO [T-29]
    }

    public function store(Request $request)
    {
        abort(501); // TODO [T-29, T-30]
    }

    public function success(string $code)
    {
        abort(501); // TODO [T-29]
    }
}
