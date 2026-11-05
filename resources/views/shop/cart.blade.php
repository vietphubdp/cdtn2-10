@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
        </ol>
    </nav>

    <h1 class="display-title page-title mb-4">Giỏ hàng của bạn</h1>

    @if ($items->isEmpty())
        <div class="empty-box text-center">
            <p class="mb-3">Giỏ hàng của bạn đang trống.</p>
            <a href="{{ route('products.index') }}" class="btn btn-dark">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Mỗi dòng: ảnh · tên + đơn giá · số lượng · thành tiền · xóa --}}
                <div class="cart-list">
                    @foreach ($items as $item)
                        @php($product = $item['product'])
                        <div class="cart-item">
                            <a href="{{ route('products.show', $product->slug) }}" class="cart-item-img">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="object-fit-cover">
                                @else
                                    <span class="text-secondary small">Ảnh</span>
                                @endif
                            </a>

                            <div class="cart-item-info">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <a href="{{ route('products.show', $product->slug) }}" class="cart-item-name">{{ $product->name }}</a>
                                    <form method="POST" action="{{ route('cart.remove', $product->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cart-remove" aria-label="Xóa {{ $product->name }} khỏi giỏ" title="Xóa">
                                            @include('partials.icon', ['name' => 'trash', 'size' => 20])
                                        </button>
                                    </form>
                                </div>
                                <div class="small text-secondary mb-2">Đơn giá: <span class="text-nowrap">@money($product->final_price)</span></div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-auto">
                                    <span class="cart-line-total">@money($item['line_total'])</span>

                                    {{-- Nút −/+ gửi số lượng mới; tối đa bằng tồn kho (BR-03) --}}
                                    <form method="POST" action="{{ route('cart.update', $product->id) }}" class="qty-stepper">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}"
                                                aria-label="Giảm số lượng" @disabled($item['quantity'] <= 1)>−</button>
                                        <span class="qty-value">{{ $item['quantity'] }}</span>
                                        <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}"
                                                aria-label="Tăng số lượng" @disabled($item['quantity'] >= $product->stock)>+</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-box">
                    <h2 class="summary-title">Tóm tắt</h2>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Tạm tính:</span>
                        <strong>@money($subtotal)</strong>
                    </div>
                    <p class="small text-secondary mb-0">Mã giảm giá và phí được tính ở bước thanh toán</p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span>Tổng:</span>
                        <strong class="fs-4">@money($subtotal)</strong>
                    </div>
                    {{-- Khách chưa đăng nhập sẽ được chuyển tới trang đăng nhập rồi quay lại thanh toán --}}
                    <a href="{{ route('checkout.index') }}" class="btn btn-dark btn-lg w-100">
                        Tiến hành đặt hàng @include('partials.icon', ['name' => 'arrow-right', 'size' => 20])
                    </a>
                </div>
            </div>
        </div>
    @endif
@endsection
