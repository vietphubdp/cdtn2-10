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
        <div class="col-lg-3">
            @include('partials.account-sidebar')
        </div>

        <div class="col-lg-9">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <h1 class="h3 fw-bold mb-0">Đơn hàng {{ $order->code }}</h1>
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
            </div>

            @if ($currentStep === false)
                <div class="alert alert-secondary">Đơn hàng đã bị hủy.</div>
            @else
                <div class="step-bar row g-0 text-center small border rounded overflow-hidden mb-3">
                    @foreach ($steps as $index => $step)
                        <div @class(['col-6 col-md-3 py-2', 'bg-dark text-white' => $index <= $currentStep, 'bg-light text-secondary' => $index > $currentStep])>
                            {{ $index <= $currentStep ? '●' : '○' }} {{ $step->label() }}
                            @if ($index === 0)
                                <div class="small">{{ $order->created_at->format('d/m H:i') }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="p-3 bg-white border rounded h-100">
                        <h2 class="h6 fw-bold text-uppercase">Người nhận</h2>
                        <div>{{ $order->receiver_name }} · {{ $order->receiver_phone }}</div>
                        <div>{{ $order->shipping_address }}</div>
                        @if ($order->note)
                            <div class="small text-secondary">Ghi chú: {{ $order->note }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-white border rounded h-100">
                        <h2 class="h6 fw-bold text-uppercase">Thanh toán</h2>
                        <div>COD – thanh toán khi nhận hàng</div>
                        @if ($order->coupon)
                            <div>Mã giảm giá: {{ $order->coupon->code }}</div>
                        @endif
                        <div class="small text-secondary">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white border rounded">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-end">Đơn giá (lúc mua)</th>
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
                                        {{-- Đơn hoàn thành → link sang khung đánh giá của SP (BR-07); SP đã ẩn thì không có link --}}
                                        @if ($order->status === \App\Enums\OrderStatus::Completed && $item->product?->is_active)
                                            <a href="{{ route('products.show', $item->product->slug) }}#danh-gia" class="d-block small">Đánh giá</a>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">@money($item->price)</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end text-nowrap">@money($item->line_total)</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">Tạm tính</td>
                                <td class="text-end text-nowrap">@money($order->subtotal)</td>
                            </tr>
                            @if ($order->discount > 0)
                                <tr>
                                    <td colspan="3" class="text-end">Giảm giá</td>
                                    <td class="text-end text-nowrap">−@money($order->discount)</td>
                                </tr>
                            @endif
                            <tr>
                                <th colspan="3" class="text-end">Tổng cộng</th>
                                <th class="text-end text-nowrap">@money($order->total)</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <a href="{{ route('orders.index') }}" class="btn btn-link px-0 mt-3">← Quay lại danh sách đơn</a>
        </div>
    </div>
@endsection
