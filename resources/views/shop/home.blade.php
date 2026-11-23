@extends('layouts.app')

@section('title', 'Trang chủ')

@php
    // Ảnh SP mới nhất (có ảnh) để trang trí banner
    $heroProducts = $newProducts->filter(fn ($product) => $product->image_url)->take(2)->values();
@endphp

@section('content')
    <div class="row g-3 g-lg-4 mb-4">
        {{-- Banner khuyến mãi (ảnh tĩnh) --}}
        <div class="col-lg-8">
            <div class="hero-banner h-100">
                <div class="row g-4 align-items-center h-100">
                    <div class="col-md-8">
                        <h1 class="display-title hero-title">Phụ kiện công nghệ <em>hợp gu của bạn</em></h1>
                        <p class="hero-text">Tai nghe, sạc dự phòng, chuột, bàn phím… chính hãng, giá tốt mỗi ngày. Thanh toán khi nhận hàng.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-light btn-lg">Mua sắm ngay @include('partials.icon', ['name' => 'arrow-right', 'size' => 18])</a>

                        <div class="hero-stats d-flex">
                            <div>
                                <div class="hero-stat-value">{{ $categories->count() }}</div>
                                <div class="hero-stat-label">Danh mục</div>
                            </div>
                            <div>
                                <div class="hero-stat-value">{{ $categories->sum('products_count') }}</div>
                                <div class="hero-stat-label">Sản phẩm đang bán</div>
                            </div>
                            <div>
                                <div class="hero-stat-value">7 ngày</div>
                                <div class="hero-stat-label">Đổi trả nếu lỗi</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 d-none d-md-block">
                        <div class="hero-visual">
                            @forelse ($heroProducts as $product)
                                <a href="{{ route('products.show', $product->slug) }}" class="hero-shot hero-shot-{{ $loop->iteration }}">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                </a>
                            @empty
                                {{-- Chưa SP nào có ảnh → giới thiệu SP mới nhất bằng thẻ chữ --}}
                                @if ($newProducts->isNotEmpty())
                                    @php($featured = $newProducts->first())
                                    <a href="{{ route('products.show', $featured->slug) }}" class="hero-feature">
                                        <span class="hero-feature-tag">Mới về</span>
                                        <span class="hero-feature-name">{{ $featured->name }}</span>
                                        <span class="hero-feature-link">Xem ngay @include('partials.icon', ['name' => 'arrow-right', 'size' => 18])</span>
                                    </a>
                                @endif
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Danh mục kèm số SP đang bán; bấm → /san-pham?category=<slug> --}}
        <div class="col-lg-4">
            <section id="danh-muc" class="category-box" aria-label="Danh mục sản phẩm">
                <ul class="category-list list-unstyled mb-0">
                    @forelse ($visibleCategories as $category)
                        <li>
                            <a class="category-row" href="{{ route('products.index', ['category' => $category->slug]) }}">
                                <span class="text-truncate">{{ $category->name }}</span>
                                <small class="visually-hidden">{{ $category->products_count }} sản phẩm</small>
                                @include('partials.icon', ['name' => 'chevron-right', 'size' => 20])
                            </a>
                        </li>
                    @empty
                        <li class="p-3 text-secondary">Chưa có danh mục.</li>
                    @endforelse
                </ul>
                @if ($categories->isNotEmpty())
                    <nav class="category-pager" aria-label="Phân trang danh mục">
                        <span aria-label="Trang {{ $categoryPage }} trên {{ $categoryPages }}">{{ $categoryPage }}/{{ $categoryPages }}</span>
                        @foreach ([-1 => 'Trang trước', 1 => 'Trang sau'] as $step => $label)
                            @php($target = $categoryPage + $step)
                            @if ($target >= 1 && $target <= $categoryPages)
                                <a class="category-step" href="{{ route('home', ['danh-muc' => $target]) }}#danh-muc" aria-label="{{ $label }}">
                                    @include('partials.icon', ['name' => 'chevron-right', 'size' => 18])
                                </a>
                            @else
                                <button class="category-step" type="button" disabled aria-label="{{ $label }}">
                                    @include('partials.icon', ['name' => 'chevron-right', 'size' => 18])
                                </button>
                            @endif
                        @endforeach
                    </nav>
                @endif
            </section>
        </div>
    </div>

    {{-- Dải cam kết của shop (trang trí) --}}
    <div class="promise-strip full-bleed mb-5">
        <div class="container">
            <ul class="list-unstyled d-flex flex-wrap justify-content-between gap-3 mb-0">
                <li>@include('partials.icon', ['name' => 'shield', 'size' => 26]) Chính hãng 100%</li>
                <li>@include('partials.icon', ['name' => 'truck', 'size' => 26]) Giao hàng toàn quốc</li>
                <li>@include('partials.icon', ['name' => 'cash', 'size' => 26]) Thanh toán khi nhận hàng</li>
                <li>@include('partials.icon', ['name' => 'refresh', 'size' => 26]) Đổi trả 7 ngày</li>
            </ul>
        </div>
    </div>

    @include('shop.partials.product-section', [
        'title' => 'Sản phẩm mới',
        'products' => $newProducts,
    ])

    <hr class="section-divider">

    @include('shop.partials.product-section', [
        'title' => 'Đang khuyến mãi',
        'products' => $saleProducts,
    ])

    {{-- Khám phá theo danh mục (trang trí): ô to/nhỏ xen kẽ --}}
    @if ($categories->isNotEmpty())
        <section class="category-bento mt-5">
            <h2 class="display-title section-title text-center mb-4">Khám phá theo danh mục</h2>
            <div class="row g-3">
                @foreach ($categories->take(4) as $category)
                    {{-- Hàng 1: nhỏ - to, hàng 2: to - nhỏ --}}
                    <div class="{{ in_array($loop->index, [1, 2]) ? 'col-md-7' : 'col-md-5' }}">
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="bento-tile">
                            <span class="bento-name">{{ $category->name }}</span>
                            <span class="bento-count">{{ $category->products_count }} sản phẩm</span>
                            @if ($cover = $category->products->first()?->image_url)
                                <img class="bento-photo" src="{{ $cover }}" alt="" loading="lazy">
                            @endif
                            <span class="bento-arrow">@include('partials.icon', ['name' => 'arrow-right', 'size' => 20])</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
