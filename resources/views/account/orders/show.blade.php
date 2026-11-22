@extends('layouts.app')

@section('title', 'Đơn hàng '.$order->code)

@php
    // Các bước của đơn bình thường; đơn đã hủy không hiện thanh này
    $steps = [
        \App\Enums\OrderStatus::Pending,
        \App\Enums\OrderStatus::Confirmed,
        \App\Enums\OrderStatus::Shipping,
        \App\Enums\OrderStatus::Completed,
    ];
    $currentStep = array_search($order->status, $steps, true);
@endphp

@section('content')
    <div class="row g-4">
        <div class="col-lg-3 order-last order-lg-first">
            @include('partials.account-sidebar')
        </div>

        <div class="col-lg-9 order-detail">
            <header class="order-heading d-flex flex-wrap align-items-center gap-3 mb-3">
                <h1 class="mb-0">Đơn hàng {{ $order->code }}</h1>
                <span class="badge text-bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span>

                {{-- BR-05: nút hủy chỉ hiện khi đơn còn chờ xác nhận --}}
                @if ($order->status->canBeCancelledByCustomer())
                    <form method="POST" action="{{ route('orders.cancel', $order->code) }}" class="ms-auto"
                          onsubmit="return confirm(@js('Bạn chắc chắn muốn hủy đơn '.$order->code.'?'))">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-danger">Hủy đơn</button>
                    </form>
                @endif
            </header>

            @if ($currentStep === false)
                <div class="alert alert-secondary">Đơn hàng đã bị hủy.</div>
            @else
                <ol class="order-track" aria-label="Tiến trình đơn hàng">
                    @foreach ($steps as $index => $step)
                        <li @class(['is-reached' => $index <= $currentStep])
                            @if ($index === $currentStep) aria-current="step" @endif>
                            <span class="order-marker" aria-hidden="true">{{ $index < $currentStep ? '✓' : $index + 1 }}</span>
                            <strong>{{ $step->label() }}</strong>
                            @if ($index === 0)
                                <small>{{ $order->created_at->format('d/m H:i') }}</small>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="order-info h-100">
                        <h2>@include('partials.icon', ['name' => 'truck']) Người nhận</h2>
                        <div>{{ $order->receiver_name }} · {{ $order->receiver_phone }}</div>
                        <div>{{ $order->shipping_address }}</div>
                        @if ($order->note)
                            <div class="small text-secondary">Ghi chú: {{ $order->note }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="order-info h-100">
                        <h2>@include('partials.icon', ['name' => 'cash']) Thanh toán</h2>
                        <div>COD – thanh toán khi nhận hàng</div>
                        @if ($order->coupon)
                            <div>Mã giảm giá: {{ $order->coupon->code }}</div>
                        @endif
                        <div class="small text-secondary">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <section class="order-receipt" aria-label="Chi tiết thanh toán">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-end d-none d-sm-table-cell">Đơn giá (lúc mua)</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Tên và giá lấy từ order_items (lưu lúc mua), không lấy từ bảng products --}}
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->product_name }}
                                        {{-- Màn hình nhỏ (< 576px) ẩn cột Đơn giá → hiện đơn giá ngay dưới tên SP --}}
                                        <div class="d-sm-none small text-secondary text-nowrap">@money($item->price)</div>
                                        {{-- Đơn hoàn thành → link sang khung đánh giá của SP (BR-07); SP đã ẩn thì không có link --}}
                                        @if ($order->status === \App\Enums\OrderStatus::Completed && $item->product?->is_active)
                                            <a href="{{ route('products.show', $item->product->slug) }}#danh-gia" class="d-block small">Đánh giá</a>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap d-none d-sm-table-cell">@money($item->price)</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end text-nowrap">@money($item->line_total)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <dl class="order-totals mb-0">
                    <div><dt>Tạm tính</dt><dd>@money($order->subtotal)</dd></div>
                    @if ($order->discount > 0)
                        <div><dt>Giảm giá</dt><dd>−@money($order->discount)</dd></div>
                    @endif
                    <div class="order-grand-total"><dt>Tổng cộng</dt><dd>@money($order->total)</dd></div>
                </dl>
            </section>

            <a href="{{ route('orders.index') }}" class="btn btn-link px-0 mt-3">← Quay lại danh sách đơn</a>
        </div>
    </div>
@endsection
