@extends('layouts.admin')

@section('title', 'Sản phẩm')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">Sản phẩm</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-dark">+ Thêm sản phẩm</a>
    </div>

    {{-- Tìm theo tên, lọc danh mục và trạng thái (Hình 14) --}}
    <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md">
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Tìm theo tên..." aria-label="Tìm theo tên">
        </div>
        <div class="col-6 col-md-auto">
            <select name="category_id" class="form-select" aria-label="Danh mục">
                <option value="">Tất cả danh mục</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-auto">
            <select name="status" class="form-select" aria-label="Trạng thái">
                <option value="">Tất cả trạng thái</option>
                <option value="active" @selected($status === 'active')>Đang hiện</option>
                <option value="hidden" @selected($status === 'hidden')>Đang ẩn</option>
            </select>
        </div>
        <div class="col-12 col-md-auto d-flex gap-2">
            <button type="submit" class="btn btn-outline-dark">Lọc</button>
            @if ($q !== '' || $categoryId > 0 || $status !== '')
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Bỏ lọc</a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên</th>
                        <th>Danh mục</th>
                        <th class="text-end">Giá</th>
                        <th class="text-center">Tồn kho</th>
                        <th>Hiển thị</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="56" height="56" class="rounded border object-fit-cover">
                                @else
                                    <div class="rounded border bg-light text-secondary small d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">Ảnh</div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td class="text-end text-nowrap">
                                @money($product->final_price)
                                @if ($product->is_on_sale)
                                    <div class="small text-secondary text-decoration-line-through">@money($product->price)</div>
                                @endif
                            </td>
                            <td @class(['text-center', 'fw-bold text-danger' => ! $product->in_stock])>{{ $product->stock }}</td>
                            <td class="text-nowrap">
                                @if ($product->is_active)
                                    <span class="badge text-bg-success">Hiện</span>
                                @else
                                    <span class="badge text-bg-secondary">Ẩn</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Sửa</a>

                                <form method="POST" action="{{ route('admin.products.active', $product) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $product->is_active ? 'Ẩn' : 'Hiện' }}</button>
                                </form>

                                {{-- SP đã có trong đơn chỉ được ẩn, không có nút Xóa --}}
                                @unless ($product->order_items_exists)
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline"
                                          onsubmit="return confirm(@js('Xóa sản phẩm "'.$product->name.'"?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">
                                {{ $q !== '' || $categoryId > 0 || $status !== '' ? 'Không có sản phẩm phù hợp bộ lọc.' : 'Chưa có sản phẩm nào.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="small text-secondary mt-2 mb-0">Nút "Xóa" chỉ có với sản phẩm chưa nằm trong đơn nào; sản phẩm đã bán chỉ có thể ẩn.</p>

    <div class="mt-3">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
@endsection
