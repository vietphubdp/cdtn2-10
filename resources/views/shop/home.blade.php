@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
    <div class="row g-3 mb-4">
        {{-- Banner khuyến mãi (ảnh tĩnh) --}}
        <div class="col-lg-8">
            <div class="h-100 p-4 p-md-5 bg-dark text-white rounded d-flex flex-column justify-content-center">
                <h1 class="h3">Phụ kiện công nghệ chính hãng</h1>
                <p class="mb-3 text-white-50">Tai nghe, sạc dự phòng, chuột, bàn phím… giá tốt mỗi ngày.</p>
                <div>
                    <a href="{{ route('products.index') }}" class="btn btn-light">Mua sắm ngay</a>
                </div>
            </div>
        </div>

        {{-- Danh mục kèm số SP đang bán; bấm → /san-pham?category=<slug> --}}
        <div class="col-lg-4">
            <div class="h-100 p-3 bg-white border rounded">
                <h2 class="h6 fw-bold text-uppercase">Danh mục</h2>
                <ul class="list-unstyled mb-0">
                    @foreach ($categories as $category)
                        <li class="py-1">
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                               class="text-dark text-decoration-none">
                                {{ $category->name }} ({{ $category->products_count }})
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    @include('shop.partials.product-section', [
        'title' => 'Sản phẩm mới',
        'products' => $newProducts,
    ])

    @include('shop.partials.product-section', [
        'title' => 'Đang khuyến mãi',
        'products' => $saleProducts,
    ])
@endsection
