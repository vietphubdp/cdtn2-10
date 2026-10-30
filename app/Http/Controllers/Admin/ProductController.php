<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Quản lý sản phẩm, upload ảnh qua ImageService (FR-10).
 * Theo Hình 14: form thêm/sửa là trang riêng (view admin.products.form dùng chung).
 */
class ProductController extends Controller
{
    public function __construct(private ImageService $images) {}

    public function index(Request $request)
    {
        // TODO [T-21]: tìm theo tên, lọc danh mục/trạng thái, nút Ẩn/Hiện, Xóa
        $products = Product::with('category')->latest('id')->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        // Mặc định SP mới được hiển thị
        return $this->formView(new Product(['is_active' => true]));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->safe()->except('image');
        $data['slug'] = $this->makeSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->store($request->file('image'));
        }

        $product = Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã thêm sản phẩm "'.$product->name.'".');
    }

    public function edit(Product $product)
    {
        return $this->formView($product);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->safe()->except('image');
        $data['is_active'] = $request->boolean('is_active');

        // Đổi tên thì sinh lại slug theo tên mới
        if ($data['name'] !== $product->name) {
            $data['slug'] = $this->makeSlug($data['name'], $product->id);
        }

        // Chọn ảnh mới thì thay ảnh cũ; không chọn thì giữ nguyên ảnh đang có
        if ($request->hasFile('image')) {
            $data['image'] = $this->images->replace($product->image, $request->file('image'));
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã cập nhật sản phẩm "'.$product->name.'".');
    }

    public function destroy(Product $product)
    {
        abort(501); // TODO [T-21]
    }

    private function formView(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    /**
     * Sinh slug từ tên, giống cách làm ở danh mục.
     * Nếu slug đã có thì thêm số phía sau: tai-nghe-sony-2, tai-nghe-sony-3...
     */
    private function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'san-pham';
        $slug = $base;
        $number = 2;

        while (Product::where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$number++;
        }

        return $slug;
    }
}
