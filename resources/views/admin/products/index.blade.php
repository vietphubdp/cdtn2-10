@extends('layouts.admin')

@section('title', 'Sản phẩm')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">Sản phẩm</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-dark">+ Thêm sản phẩm</a>
    </div>

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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Chưa có sản phẩm nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
@endsection
