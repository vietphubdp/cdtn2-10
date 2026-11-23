<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Trang chủ: SP mới, SP khuyến mãi (FR-03, T-08).
     */
    public function index(Request $request)
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
            ->with(['products' => fn ($query) => $query->active()
                ->whereNotNull('image')->where('image', '!=', '')
                ->oldest('id')->limit(1)->select('id', 'category_id', 'image')])
            ->orderBy('name')
            ->get();

        $perPage = 6;
        $categoryPages = max(1, (int) ceil($categories->count() / $perPage));
        $categoryPage = min($categoryPages, max(1, $request->integer('danh-muc', 1)));
        $visibleCategories = $categories->forPage($categoryPage, $perPage);

        return view('shop.home', compact('newProducts', 'saleProducts', 'categories', 'visibleCategories', 'categoryPage', 'categoryPages'));
    }
}
