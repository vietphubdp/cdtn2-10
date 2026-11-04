@extends('layouts.admin')

@section('title', 'Đơn hàng '.$order->code)

@php
    // BR-05: ô chọn chỉ liệt kê các trạng thái kế tiếp hợp lệ
    $nextStatuses = $order->status->nextStatuses();
@endphp

@section('content')
    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">Đơn hàng {{ $order->code }}</h1>
        <span class="badge text-bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-uppercase">Người nhận</h2>
                    <div>{{ $order->receiver_name }} · {{ $order->receiver_phone }}</div>
                    <div>{{ $order->shipping_address }}</div>
                    <div class="small text-secondary">
                        @if ($order->note)
                            Ghi chú: {{ $order->note }} ·
                        @endif
                        Đặt lúc {{ $order->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="small text-secondary">Thanh toán: COD – thanh toán khi nhận hàng</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-uppercase">Cập nhật trạng thái</h2>

                    @if ($nextStatuses === [])
                        <p class="text-secondary mb-0">Đơn đã ở trạng thái cuối "{{ $order->status->label() }}", không thể đổi nữa.</p>
                    @else
                        {{-- Chọn "Đã hủy" thì hỏi lại trước khi gửi vì sẽ hoàn kho (BR-06) --}}
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}"
                              onsubmit="return this.status.value !== 'cancelled' || confirm(@js('Hủy đơn '.$order->code.' và cộng lại tồn kho?'))">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select @error('status') is-invalid @enderror" aria-label="Trạng thái mới">
                                @foreach ($nextStatuses as $next)
                                    <option value="{{ $next->value }}" @selected(old('status') === $next->value)>{{ $next->label() }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Lựa chọn hợp lệ: {{ collect($nextStatuses)->map->label()->join(' · ') }}
                            </div>
                            <button type="submit" class="btn btn-dark mt-2">Cập nhật</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
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
                            <td>{{ $item->product_name }}</td>
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
                            <td colspan="3" class="text-end">Giảm giá{{ $order->coupon ? ' ('.$order->coupon->code.')' : '' }}</td>
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

    <a href="{{ route('admin.orders.index') }}" class="btn btn-link px-0 mt-3">← Quay lại danh sách đơn</a>
@endsection
