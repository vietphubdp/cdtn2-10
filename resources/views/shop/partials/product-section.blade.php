{{--
    Một khu SP trên trang chủ: tiêu đề + "Xem tất cả" + lưới thẻ SP.
    Cách dùng: @include('shop.partials.product-section', ['title' => '...', 'products' => $products])
--}}
<section class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">{{ $title }}</h2>
        <a href="{{ route('products.index') }}" class="text-dark text-decoration-none">Xem tất cả &rarr;</a>
    </div>

    @if ($products->isEmpty())
        <p class="text-secondary">Chưa có sản phẩm.</p>
    @else
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach ($products as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    @endif
</section>
