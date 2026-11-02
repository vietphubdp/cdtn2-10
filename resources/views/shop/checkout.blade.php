@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
    <form method="POST" action="{{ route('checkout.store') }}" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <h1 class="h3 fw-bold mb-3">Thông tin giao hàng</h1>

                {{-- Các bước đặt hàng --}}
                <div class="row g-0 text-center small border rounded overflow-hidden mb-3">
                    <div class="col-4 py-2 bg-light">
                        <a href="{{ route('cart.index') }}" class="text-dark">1. Giỏ hàng</a>
                    </div>
                    <div class="col-4 py-2 bg-dark text-white">2. Thông tin giao hàng</div>
                    <div class="col-4 py-2 bg-light text-secondary">3. Hoàn tất</div>
                </div>

                <div class="p-3 bg-white border rounded">
                    {{-- Điền sẵn từ hồ sơ; khách sửa được cho đơn này --}}
                    <div class="mb-3">
                        <label for="receiver_name" class="form-label">Họ tên người nhận <span class="text-danger">*</span></label>
                        <input type="text" id="receiver_name" name="receiver_name" value="{{ old('receiver_name', $user->name) }}"
                               class="form-control @error('receiver_name') is-invalid @enderror" required>
                        @error('receiver_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="receiver_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" id="receiver_phone" name="receiver_phone" value="{{ old('receiver_phone', $user->phone) }}"
                               class="form-control @error('receiver_phone') is-invalid @enderror"
                               placeholder="09xxxxxxxx" required>
                        @error('receiver_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                        <input type="text" id="shipping_address" name="shipping_address" value="{{ old('shipping_address', $user->address) }}"
                               class="form-control @error('shipping_address') is-invalid @enderror"
                               placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" required>
                        @error('shipping_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Ghi chú</label>
                        <textarea id="note" name="note" rows="3"
                                  class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <p class="mb-0">
                        <strong>Phương thức thanh toán:</strong>
                        <span class="form-check d-inline-block ms-1 mb-0">
                            <input type="radio" id="payment_cod" class="form-check-input" checked disabled>
                            <label for="payment_cod" class="form-check-label">Thanh toán khi nhận hàng (COD)</label>
                        </span>
                    </p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="p-3 bg-white border rounded">
                    <h2 class="h6 fw-bold text-uppercase">Đơn hàng ({{ $items->count() }} SP)</h2>
                    @foreach ($items as $item)
                        <div class="d-flex justify-content-between gap-2 small mb-1">
                            <span>{{ $item['product']->name }} × {{ $item['quantity'] }}</span>
                            <span class="text-nowrap">@money($item['line_total'])</span>
                        </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Tạm tính:</span>
                        <span>@money($subtotal)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Tổng cộng:</strong>
                        <strong class="fs-5">@money($subtotal)</strong>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Đặt hàng</button>
                </div>
            </div>
        </div>
    </form>
@endsection
