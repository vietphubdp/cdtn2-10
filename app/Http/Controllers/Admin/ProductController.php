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

    /**
     * Danh sách SP (gồm cả SP ẩn): tìm theo tên, lọc danh mục và trạng thái hiển thị.
     */
    public function index(Request $request)
    {
        // Tham số sai kiểu hoặc giá trị lạ coi như không lọc
        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $categoryId = $request->integer('category_id');
        $status = in_array($request->query('status'), ['active', 'hidden'], true) ? $request->query('status') : '';

        $products = Product::with('category')
            ->withExists('orderItems') // order_items_exists: SP đã có trong đơn thì không hiện nút Xóa
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'q', 'categoryId', 'status'));
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

    /**
     * Ẩn/hiện SP với khách (BR-02). Quay lại đúng trang đang xem để giữ bộ lọc.
     */
    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        $message = $product->is_active
            ? 'Đã hiện sản phẩm "'.$product->name.'".'
            : 'Đã ẩn sản phẩm "'.$product->name.'" khỏi trang khách.';

        return back()->with('success', $message);
    }

    public function destroy(Product $product)
    {
        // SP đã nằm trong đơn thì chỉ được ẩn, không xóa (order_items RESTRICT)
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Không thể xóa "'.$product->name.'": sản phẩm đã có trong đơn hàng, chỉ có thể ẩn.');
        }

        $product->delete();
        $this->images->delete($product->image);

        return back()->with('success', 'Đã xóa sản phẩm "'.$product->name.'".');
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
