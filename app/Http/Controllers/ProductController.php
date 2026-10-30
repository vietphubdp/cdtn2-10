<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Danh sách SP: tìm kiếm, lọc, sắp xếp, phân trang 12 SP (FR-03, FR-04).
     */
    public function index(Request $request)
    {
        // Tham số sai kiểu (vd. ?q[]=...) hoặc giá nhập sai (chữ, số âm) coi như không lọc
        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $categorySlug = is_string($request->query('category')) ? $request->query('category') : '';
        $minPrice = $request->integer('min_price') > 0 ? $request->integer('min_price') : null;
        $maxPrice = $request->integer('max_price') > 0 ? $request->integer('max_price') : null;
        $sort = in_array($request->query('sort'), ['price_asc', 'price_desc'], true) ? $request->query('sort') : 'latest';

        $categories = Category::orderBy('name')->get();
        $currentCategory = $categories->firstWhere('slug', $categorySlug);

        // Giá bán = giá KM nếu có, ngược lại giá gốc (BR-01) → lọc và sắp xếp theo giá bán
        $finalPrice = 'COALESCE(sale_price, price)';

        $products = Product::active()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->when($categorySlug !== '', fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->when($minPrice, fn ($query) => $query->whereRaw("$finalPrice >= ?", [$minPrice]))
            ->when($maxPrice, fn ($query) => $query->whereRaw("$finalPrice <= ?", [$maxPrice]))
            ->when($sort === 'price_asc', fn ($query) => $query->orderByRaw("$finalPrice asc"))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByRaw("$finalPrice desc"))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('shop.products.index', compact('products', 'categories', 'currentCategory', 'q', 'minPrice', 'maxPrice', 'sort'));
    }

    /**
     * Chi tiết SP; SP ẩn → 404 (FR-05, BR-02).
     */
    public function show(string $slug)
    {
        // Chỉ tìm trong SP đang bán: SP ẩn hoặc slug sai → 404 (BR-02)
        $product = Product::active()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        // Đánh giá chỉ hiển thị; form viết đánh giá làm ở FR-12
        $reviews = $product->reviews()->with('user')->latest('id')->get();

        return view('shop.products.show', compact('product', 'reviews'));
    }
}
