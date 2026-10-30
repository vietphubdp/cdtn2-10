<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    /**
     * Trang chủ: SP mới, SP khuyến mãi (FR-03, T-08).
     */
    public function index()
    {
        // Chỉ hiện SP đang bán (BR-02), mỗi khu tối đa 8 SP
        $newProducts = Product::active()->latest('id')->take(8)->get();

        $saleProducts = Product::active()
            ->whereNotNull('sale_price')
            ->latest('id')
            ->take(8)
            ->get();

        // Khung "Danh mục": đếm số SP đang bán của từng danh mục
        $categories = Category::withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('name')
            ->get();

        return view('shop.home', compact('newProducts', 'saleProducts', 'categories'));
    }
}
