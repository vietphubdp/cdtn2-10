@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
    <form class="checkout" method="POST" action="{{ route('checkout.store') }}" novalidate>
        @csrf

        <header class="checkout-heading">
            <h1>Thông tin giao hàng</h1>
            <ol class="checkout-steps" aria-label="Tiến trình đặt hàng">
                <li><a href="{{ route('cart.index') }}"><b aria-hidden="true">✓</b> Giỏ hàng</a></li>
                <li aria-current="step"><b>2</b> Giao hàng</li>
                <li><b>3</b> Hoàn tất</li>
            </ol>
        </header>
        <div class="row g-4">
            <div class="col-lg-7">
                <section class="checkout-panel">
                    <h2>@include('partials.icon', ['name' => 'truck']) Người nhận</h2>
                    <div class="checkout-fields">
                        {{-- Điền sẵn từ hồ sơ; khách sửa được cho đơn này --}}
                        <div class="mb-3">
                            <label for="receiver_name" class="form-label">Họ tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" id="receiver_name" name="receiver_name" autocomplete="shipping name" value="{{ old('receiver_name', $user->name) }}"
                                   class="form-control @error('receiver_name') is-invalid @enderror" required>
                            @error('receiver_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="receiver_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" id="receiver_phone" name="receiver_phone" autocomplete="shipping tel" value="{{ old('receiver_phone', $user->phone) }}"
                                   class="form-control @error('receiver_phone') is-invalid @enderror"
                                   placeholder="09xxxxxxxx" required>
                            @error('receiver_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                        <input type="text" id="shipping_address" name="shipping_address" autocomplete="shipping street-address" value="{{ old('shipping_address', $user->address) }}"
                               class="form-control @error('shipping_address') is-invalid @enderror"
                               placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" required>
                        @error('shipping_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Ghi chú <span class="text-secondary small">(không bắt buộc)</span></label>
                        <textarea id="note" name="note" rows="3"
                                  class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="checkout-payment">
                        @include('partials.icon', ['name' => 'cash', 'size' => 24])
                        <div><strong>Phương thức thanh toán</strong><br>Thanh toán khi nhận hàng (COD)</div>
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                <section class="checkout-panel checkout-summary">
                    <h2>@include('partials.icon', ['name' => 'cart']) Đơn hàng <small>({{ $items->sum('quantity') }} sản phẩm)</small></h2>
                    @foreach ($items as $item)
                        <div class="checkout-item">
                            <span class="checkout-thumb" aria-hidden="true">
                                @if ($item['product']->image_url)
                                    <img src="{{ $item['product']->image_url }}" alt="">
                                @else
                                    @include('partials.icon', ['name' => 'cart'])
                                @endif
                            </span>
                            <div>{{ $item['product']->name }} × {{ $item['quantity'] }}</div>
                            <strong>@money($item['line_total'])</strong>
                        </div>
                    @endforeach
                    <hr>

                    {{-- Mã giảm giá (Hình 7): ô nhập thuộc form riêng #coupon-form ở cuối trang (form không lồng nhau được) --}}
                    @if ($coupon)
                        <div class="d-flex justify-content-between align-items-center gap-2 small mb-2">
                            <span>Mã <strong>{{ $coupon->code }}</strong> · giảm {{ $coupon->describe() }}</span>
                            <button type="submit" form="coupon-remove-form" class="btn btn-sm btn-link text-danger p-0">Bỏ mã</button>
                        </div>
                    @else
                        <label for="code" class="form-label small mb-1">Mã giảm giá</label>
                        <div class="input-group input-group-sm has-validation mb-2">
                            <input type="text" id="code" name="code" value="{{ old('code') }}" form="coupon-form" maxlength="30"
                                   class="form-control text-uppercase @error('code') is-invalid @enderror" placeholder="Nhập mã">
                            <button type="submit" form="coupon-form" class="btn btn-outline-dark">Áp dụng</button>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mt-3">
                        <span>Tạm tính:</span>
                        <span>@money($subtotal)</span>
                    </div>
                    @if ($discount > 0)
                        <div class="d-flex justify-content-between text-success">
                            <span>Giảm giá:</span>
                            <span>−@money($discount)</span>
                        </div>
                    @endif
                    <div class="checkout-total d-flex justify-content-between align-items-center mb-3">
                        <strong>Tổng cộng:</strong>
                        <strong class="fs-5">@money($total)</strong>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Đặt hàng</button>
                </section>
            </div>
        </div>
    </form>

    <form id="coupon-form" method="POST" action="{{ route('checkout.coupon.apply') }}" novalidate>
        @csrf
    </form>
    <form id="coupon-remove-form" method="POST" action="{{ route('checkout.coupon.remove') }}">
        @csrf
        @method('DELETE')
    </form>
@endsection
