{{--
    Thẻ sản phẩm dùng chung (trang chủ, danh sách SP).
    Cách dùng: @include('partials.product-card', ['product' => $product])
--}}
<div class="product-card h-100 d-flex flex-column">
    <a href="{{ route('products.show', $product->slug) }}" class="product-card-img ratio ratio-1x1">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="object-fit-cover" loading="lazy">
        @else
            <span class="d-flex align-items-center justify-content-center text-secondary small">Chưa có ảnh</span>
        @endif
    </a>

    <div class="product-card-body d-flex flex-column flex-grow-1">
        <a href="{{ route('products.show', $product->slug) }}" class="product-card-name">
            {{ $product->name }}
        </a>

        {{-- Giá bán = giá KM nếu có (BR-01); có KM thì gạch giá gốc + nhãn % giảm --}}
        <div class="price-row mt-auto mb-3">
            <span class="price">@money($product->final_price)</span>
            @if ($product->is_on_sale)
                <span class="price-old">@money($product->price)</span>
                <span class="price-off">-{{ round(($product->price - $product->final_price) * 100 / $product->price) }}%</span>
            @endif
        </div>

        {{-- SP hết hàng vẫn hiện nhưng không cho thêm vào giỏ (BR-02) --}}
        @if ($product->in_stock)
            <form method="POST" action="{{ route('cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-outline-dark w-100">Thêm vào giỏ</button>
            </form>
        @else
            <button type="button" class="btn btn-outline-secondary w-100" disabled>Hết hàng</button>
        @endif
    </div>
</div>
