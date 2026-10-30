@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-5">
            <div class="ratio ratio-1x1 bg-body-secondary border rounded overflow-hidden">
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

            <h1 class="h3 fw-bold">{{ $product->name }}</h1>

            {{-- Điểm trung bình làm tròn, hiện dạng sao --}}
            <div class="mb-2 text-secondary">
                @if ($reviews->isNotEmpty())
                    @php($avgRating = (int) round($reviews->avg('rating')))
                    <span class="text-warning">{{ str_repeat('★', $avgRating) . str_repeat('☆', 5 - $avgRating) }}</span>
                    ({{ $reviews->count() }} đánh giá)
                @else
                    Chưa có đánh giá
                @endif
            </div>

            {{-- Giá bán = giá KM nếu có (BR-01); có KM thì gạch giá gốc --}}
            <div class="mb-3">
                <span class="fs-3 fw-bold">@money($product->final_price)</span>
                @if ($product->is_on_sale)
                    <span class="text-secondary text-decoration-line-through ms-2">@money($product->price)</span>
                @endif
            </div>

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
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <label for="quantity" class="form-label mb-0">Số lượng:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                               class="form-control" style="width: 6rem">
                    </div>
                    <button type="submit" class="btn btn-dark px-4">Thêm vào giỏ hàng</button>
                </form>
            @else
                <button type="button" class="btn btn-secondary px-4 mb-4" disabled>Hết hàng</button>
            @endif

            <div class="p-3 bg-white border rounded">
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

    <section class="p-3 bg-white border rounded">
        <h2 class="h6 fw-bold text-uppercase">Đánh giá của khách hàng</h2>

        @forelse ($reviews as $review)
            <div class="py-2 @if (! $loop->last) border-bottom @endif">
                <span class="text-warning">{{ str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating) }}</span>
                <strong>{{ $review->user->name }}</strong>
                @if ($review->comment)
                    – {{ $review->comment }}
                @endif
            </div>
        @empty
            <p class="mb-0 text-secondary">Sản phẩm chưa có đánh giá nào.</p>
        @endforelse
    </section>
@endsection
