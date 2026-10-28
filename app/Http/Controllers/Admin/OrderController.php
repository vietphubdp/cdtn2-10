<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Quản lý đơn hàng, đổi trạng thái theo OrderStatus (FR-11).
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        abort(501); // TODO [T-35]
    }

    public function show(Order $order)
    {
        abort(501); // TODO [T-36]
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort(501); // TODO [T-36]
    }
}
