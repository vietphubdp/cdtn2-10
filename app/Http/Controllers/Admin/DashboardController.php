<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Trang tổng quan quản trị (tạm): đếm số bản ghi.
     * Thống kê doanh thu, top SP bán chạy làm ở Sprint 3 (FR-13).
     */
    public function index()
    {
        return view('admin.dashboard', [
            'categoryCount' => Category::count(),
            'productCount' => Product::count(),
            'orderCount' => Order::count(),
            'customerCount' => User::where('role', User::ROLE_CUSTOMER)->count(),
        ]);
    }
}
