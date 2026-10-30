<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Support\Str;

/**
 * Quản lý danh mục (FR-09).
 * Theo Hình 13: bảng danh mục và form thêm/sửa nằm chung 1 trang.
 */
class CategoryController extends Controller
{
    public function index()
    {
        return $this->listView(null);
    }

    // Form thêm đã nằm sẵn ở trang danh sách
    public function create()
    {
        return redirect()->route('admin.categories.index');
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->makeSlug($data['name']);

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã thêm danh mục "'.$data['name'].'".');
    }

    public function edit(Category $category)
    {
        return $this->listView($category);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        // Đổi tên thì sinh lại slug theo tên mới
        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->makeSlug($data['name'], $category->id);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã cập nhật danh mục "'.$category->name.'".');
    }

    public function destroy(Category $category)
    {
        // FR-09: không xóa danh mục còn sản phẩm (kể cả SP đang ẩn) – khớp ràng buộc RESTRICT trong CSDL
        $productCount = $category->products()->count();

        if ($productCount > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Không thể xóa "'.$category->name.'": danh mục còn '.$productCount.' sản phẩm.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã xóa danh mục "'.$category->name.'".');
    }

    /**
     * Trang danh sách; $editing khác null thì form bên phải là form sửa.
     */
    private function listView(?Category $editing)
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories', 'editing'));
    }

    /**
     * Sinh slug từ tên ("Sạc dự phòng" → "sac-du-phong").
     * Nếu slug đã có thì thêm số phía sau: sac-du-phong-2, sac-du-phong-3...
     */
    private function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'danh-muc';
        $slug = $base;
        $number = 2;

        while (Category::where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$number++;
        }

        return $slug;
    }
}
