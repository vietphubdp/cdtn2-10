{{--
    Thẻ sản phẩm dùng chung (trang chủ, danh sách SP).
    Cách dùng: @include('partials.product-card', ['product' => $product])
--}}
<div class="card h-100">
    <a href="{{ route('products.show', $product->slug) }}" class="ratio ratio-1x1 bg-body-secondary">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="card-img-top object-fit-cover">
        @else
            <span class="d-flex align-items-center justify-content-center text-secondary small">Chưa có ảnh</span>
        @endif
    </a>

    <div class="card-body d-flex flex-column">
        <a href="{{ route('products.show', $product->slug) }}" class="text-dark text-decoration-none mb-2">
            {{ $product->name }}
        </a>

        {{-- Giá bán = giá KM nếu có (BR-01); có KM thì gạch giá gốc --}}
        <div class="mb-3 mt-auto">
            <span class="fw-bold">@money($product->final_price)</span>
            @if ($product->is_on_sale)
                <small class="text-secondary text-decoration-line-through ms-1">@money($product->price)</small>
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
