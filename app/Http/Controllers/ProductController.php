<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Danh sách SP: tìm kiếm, lọc, sắp xếp, phân trang 12 SP (FR-03, FR-04).
     */
    public function index(Request $request)
    {
        // TODO [T-10, T-11, T-12]: tìm theo tên, lọc danh mục + khoảng giá, sắp xếp
        $products = Product::active()
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('shop.products.index', compact('products'));
    }

    /**
     * Chi tiết SP; SP ẩn → 404 (FR-05, BR-02).
     */
    public function show(string $slug)
    {
        abort(501); // TODO [T-14, T-15]
    }
}
