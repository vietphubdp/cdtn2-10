@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="success-icon display-4">✓</div>
            <h1 class="h3 fw-bold">Đặt hàng thành công!</h1>
            <p>Cảm ơn bạn đã mua hàng tại website. Shop sẽ gọi xác nhận trong thời gian sớm nhất.</p>

            <div class="p-3 bg-white border rounded text-start mb-3">
                <dl class="row mb-0">
                    <dt class="col-sm-5 fw-normal">Mã đơn hàng</dt>
                    <dd class="col-sm-7 text-sm-end fw-bold">{{ $order->code }}</dd>

                    <dt class="col-sm-5 fw-normal">Trạng thái</dt>
                    <dd class="col-sm-7 text-sm-end">
                        <span class="badge text-bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span>
                    </dd>

                    <dt class="col-sm-5 fw-normal">Tổng thanh toán</dt>
                    <dd class="col-sm-7 text-sm-end fw-bold">
                        @money($order->total)
                        @if ($order->discount > 0)
                            <div class="small fw-normal text-success">Đã giảm @money($order->discount)</div>
                        @endif
                    </dd>

                    <dt class="col-sm-5 fw-normal">Phương thức</dt>
                    <dd class="col-sm-7 text-sm-end">Thanh toán khi nhận hàng (COD)</dd>

                    <dt class="col-sm-5 fw-normal">Giao tới</dt>
                    <dd class="col-sm-7 text-sm-end mb-0">
                        {{ $order->receiver_name }} · {{ $order->receiver_phone }} · {{ $order->shipping_address }}
                    </dd>
                </dl>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('orders.show', $order->code) }}" class="btn btn-dark">Xem chi tiết đơn hàng</a>
                <a href="{{ route('products.index') }}" class="btn btn-outline-dark">Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
@endsection
