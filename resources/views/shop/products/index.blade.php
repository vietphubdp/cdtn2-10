@extends('layouts.app')

@section('title', $currentCategory?->name ?? 'Sản phẩm')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-3">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $currentCategory?->name ?? 'Sản phẩm' }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Cột lọc (Hình 3): chọn 1 danh mục + khoảng giá. Màn hình nhỏ thu gọn sau nút "Bộ lọc" --}}
        <aside class="col-md-4 col-lg-3">
            <button class="btn btn-outline-secondary w-100 d-md-none" type="button"
                    data-bs-toggle="collapse" data-bs-target="#product-filter" aria-expanded="false" aria-controls="product-filter">
                @include('partials.icon', ['name' => 'filter', 'size' => 18]) Bộ lọc
            </button>

            <form id="product-filter" action="{{ route('products.index') }}" method="GET"
                  class="filter-panel collapse d-md-block mt-2 mt-md-0">
                {{-- Giữ từ khóa và cách sắp xếp đang chọn khi bấm "Lọc" --}}
                @if ($q !== '')
                    <input type="hidden" name="q" value="{{ $q }}">
                @endif
                @if ($sort !== 'latest')
                    <input type="hidden" name="sort" value="{{ $sort }}">
                @endif

                <div class="filter-head d-none d-md-flex">
                    <span>Bộ lọc</span>
                    @include('partials.icon', ['name' => 'filter', 'size' => 20])
                </div>

                <div class="filter-group">
                    <h2 class="filter-title">Danh mục</h2>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" value="" id="category-all"
                               @checked(! $currentCategory)>
                        <label class="form-check-label" for="category-all">Tất cả</label>
                    </div>
                    @foreach ($categories as $category)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" value="{{ $category->slug }}"
                                   id="category-{{ $category->id }}" @checked($currentCategory?->is($category))>
                            <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h2 class="filter-title">Khoảng giá</h2>
                    <input type="number" name="min_price" value="{{ $minPrice }}" min="0" step="1000"
                           class="form-control mb-2" placeholder="Từ ... đ" aria-label="Giá từ">
                    <input type="number" name="max_price" value="{{ $maxPrice }}" min="0" step="1000"
                           class="form-control" placeholder="Đến ... đ" aria-label="Giá đến">
                </div>

                <button type="submit" class="btn btn-dark w-100">Lọc</button>
                <a href="{{ route('products.index') }}" class="btn btn-link text-secondary w-100 mt-1">Xóa bộ lọc</a>
            </form>
        </aside>

        <div class="col-md-8 col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                {{-- Tiêu đề theo danh mục đang chọn, hoặc theo từ khóa tìm kiếm --}}
                <h1 class="listing-title mb-0">{{ $currentCategory?->name ?? ($q !== '' ? 'Kết quả tìm kiếm "'.$q.'"' : 'Tất cả sản phẩm') }} ({{ $products->total() }} sản phẩm)</h1>

                <div class="d-flex align-items-center gap-2">
                    @if ($products->total() > 0)
                        <span class="small text-secondary d-none d-lg-inline">
                            Hiển thị {{ $products->firstItem() }}–{{ $products->lastItem() }} / {{ $products->total() }}
                        </span>
                    @endif

                    {{-- Đổi cách sắp xếp thì gửi lại kèm bộ lọc hiện tại, về trang 1 --}}
                    <form action="{{ route('products.index') }}" method="GET">
                        @foreach (['q' => $q, 'category' => $currentCategory?->slug, 'min_price' => $minPrice, 'max_price' => $maxPrice] as $name => $value)
                            @if (filled($value))
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <select name="sort" class="form-select form-select-sm sort-select" aria-label="Sắp xếp" onchange="this.form.submit()">
                            <option value="latest" @selected($sort === 'latest')>Sắp xếp: Mới nhất</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>Sắp xếp: Giá tăng dần</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>Sắp xếp: Giá giảm dần</option>
                        </select>
                        <noscript><button type="submit" class="btn btn-outline-secondary btn-sm mt-1">Áp dụng</button></noscript>
                    </form>
                </div>
            </div>

            @if ($products->isEmpty())
                <div class="empty-box text-center text-secondary">
                    Không tìm thấy sản phẩm phù hợp
                </div>
            @else
                {{-- Lưới 12 SP/trang; màn hình nhỏ 2 cột --}}
                <div class="row row-cols-2 row-cols-lg-3 g-3 g-lg-4 mb-4">
                    @foreach ($products as $product)
                        <div class="col">
                            @include('partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                {{-- withQueryString() ở controller → sang trang khác vẫn giữ bộ lọc --}}
                <div class="listing-pagination d-flex justify-content-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection
