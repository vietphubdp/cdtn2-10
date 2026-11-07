@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
    <div class="row g-4">
        <div class="col-lg-3">
            @include('partials.account-sidebar')
        </div>

        <div class="col-lg-9">
            <h1 class="h3 fw-bold mb-3">Đơn hàng của tôi</h1>

            {{-- Lọc theo trạng thái --}}
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('orders.index') }}" @class(['btn btn-sm', 'btn-dark' => ! $currentStatus, 'btn-outline-secondary' => $currentStatus])>Tất cả</a>
                @foreach (\App\Enums\OrderStatus::cases() as $status)
                    <a href="{{ route('orders.index', ['status' => $status->value]) }}"
                       @class(['btn btn-sm', 'btn-dark' => $currentStatus === $status, 'btn-outline-secondary' => $currentStatus !== $status])>{{ $status->label() }}</a>
                @endforeach
            </div>

            @if ($orders->isEmpty())
                <div class="p-4 bg-white border rounded text-center">
                    <p class="mb-3">{{ $currentStatus ? 'Không có đơn hàng nào ở trạng thái "'.$currentStatus->label().'".' : 'Bạn chưa có đơn hàng nào.' }}</p>
                    <a href="{{ route('products.index') }}" class="btn btn-dark">Mua sắm ngay</a>
                </div>
            @else
                <div class="bg-white border rounded">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th class="d-none d-sm-table-cell">Ngày đặt</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th class="d-none d-sm-table-cell">Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="text-nowrap">
                                            <a href="{{ route('orders.show', $order->code) }}" class="fw-semibold text-dark">{{ $order->code }}</a>
                                            {{-- Màn hình nhỏ (< 576px) ẩn cột Trạng thái → hiện trạng thái ngay dưới mã đơn --}}
                                            <div class="d-sm-none"><span class="badge text-bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span></div>
                                        </td>
                                        <td class="d-none d-sm-table-cell">{{ $order->created_at->format('d/m/Y') }}</td>
                                        <td class="text-end text-nowrap">@money($order->total)</td>
                                        <td class="d-none d-sm-table-cell"><span class="badge text-bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('orders.show', $order->code) }}">Xem</a>
                                            @if ($order->status->canBeCancelledByCustomer())
                                                <span class="d-none d-sm-inline">·</span>
                                                {{-- Màn hình nhỏ: "Hủy đơn" xuống dòng dưới "Xem" để không bị khuất --}}
                                                <form method="POST" action="{{ route('orders.cancel', $order->code) }}" class="d-block d-sm-inline"
                                                      onsubmit="return confirm(@js('Hủy đơn '.$order->code.'?'))">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-link p-0 align-baseline text-danger">Hủy đơn</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection
