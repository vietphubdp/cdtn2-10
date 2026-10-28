<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Giỏ hàng lưu trong session, mọi xử lý đi qua CartService (FR-06).
 */
class CartController extends Controller
{
    public function index()
    {
        abort(501); // TODO [T-24, T-26]
    }

    public function add(Request $request)
    {
        abort(501); // TODO [T-25]
    }

    public function update(Request $request, int $productId)
    {
        abort(501); // TODO [T-24]
    }

    public function remove(int $productId)
    {
        abort(501); // TODO [T-24]
    }
}
