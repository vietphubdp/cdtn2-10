@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="row g-4 g-lg-5 mb-5">
        <div class="col-md-5">
            <div class="detail-image ratio ratio-1x1">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="object-fit-cover">
                @else
                    <span class="d-flex align-items-center justify-content-center text-secondary">Chưa có ảnh</span>
                @endif
            </div>
        </div>

        <div class="col-md-7">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>

            <h1 class="display-title detail-title">{{ $product->name }}</h1>

            {{-- Điểm trung bình làm tròn, hiện dạng sao --}}
            <div class="detail-rating mb-3">
                @if ($reviews->isNotEmpty())
                    @php($avgRating = (int) round($reviews->avg('rating')))
                    <span class="stars">{{ str_repeat('★', $avgRating) . str_repeat('☆', 5 - $avgRating) }}</span>
                    <span>{{ number_format($reviews->avg('rating'), 1, ',', '.') }}/5</span>
                    <span class="text-secondary">({{ $reviews->count() }} đánh giá)</span>
                @else
                    <span class="text-secondary">Chưa có đánh giá</span>
                @endif
            </div>

            {{-- Giá bán = giá KM nếu có (BR-01); có KM thì gạch giá gốc --}}
            <div class="price-row price-row-lg mb-3">
                <span class="price">@money($product->final_price)</span>
                @if ($product->is_on_sale)
                    <span class="price-old">@money($product->price)</span>
                    <span class="price-off">-{{ round(($product->price - $product->final_price) * 100 / $product->price) }}%</span>
                @endif
            </div>

            <hr>

            <p class="mb-3">
                Tình trạng:
                @if ($product->in_stock)
                    <strong>Còn {{ $product->stock }} sản phẩm</strong>
                @else
                    <strong class="text-danger">Hết hàng</strong>
                @endif
            </p>

            {{-- Hết hàng: khóa nút, không cho thêm vào giỏ (BR-02); số lượng tối đa = tồn kho (BR-03) --}}
            @if ($product->in_stock)
                <form method="POST" action="{{ route('cart.add') }}" class="mb-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <label for="quantity" class="form-label">Số lượng:</label>
                    <div class="d-flex flex-wrap gap-2 gap-sm-3">
                        <div class="qty-stepper qty-stepper-lg">
                            <button type="button" data-qty-step="-1" aria-label="Giảm số lượng">−</button>
                            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="{{ $product->stock }}"
                                   class="@error('quantity') is-invalid @enderror" inputmode="numeric">
                            <button type="button" data-qty-step="1" aria-label="Tăng số lượng">+</button>
                        </div>
                        <button type="submit" class="btn btn-dark btn-lg flex-grow-1">Thêm vào giỏ hàng</button>
                    </div>
                    @error('quantity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </form>
            @else
                <button type="button" class="btn btn-secondary btn-lg w-100 mb-4" disabled>Hết hàng</button>
            @endif

            <div class="info-box">
                <h2 class="h6 fw-bold text-uppercase">Mô tả</h2>
                @if ($product->description)
                    {{-- Giữ xuống dòng của mô tả, vẫn escape HTML --}}
                    <p class="mb-0">{!! nl2br(e($product->description)) !!}</p>
                @else
                    <p class="mb-0 text-secondary">Chưa có mô tả</p>
                @endif
            </div>
        </div>
    </div>

    <section class="reviews">
        <div class="reviews-head d-flex align-items-center gap-2 mb-4">
            <h2 class="h4 fw-bold mb-0">Đánh giá của khách hàng</h2>
            <span class="text-secondary">({{ $reviews->count() }})</span>
        </div>

        @if ($reviews->isEmpty())
            <p class="empty-box text-center text-secondary mb-0">Sản phẩm chưa có đánh giá nào.</p>
        @else
            <div class="row row-cols-1 row-cols-md-2 g-3">
                @foreach ($reviews as $review)
                    <div class="col">
                        <article class="review-card h-100">
                            <div class="stars mb-2">{{ str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating) }}</div>
                            {{-- BR-07: chỉ khách đã nhận hàng mới đánh giá được → gắn dấu "đã mua" --}}
                            <div class="review-author mb-2">
                                <strong>{{ $review->user->name }}</strong>
                                <span class="verified" title="Đã mua sản phẩm này">@include('partials.icon', ['name' => 'check-circle', 'size' => 18])</span>
                            </div>
                            @if ($review->comment)
                                <p class="text-secondary mb-3">{{ $review->comment }}</p>
                            @endif
                            @if ($review->created_at)
                                <div class="small text-secondary">Đăng ngày {{ $review->created_at->format('d/m/Y') }}</div>
                            @endif
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
