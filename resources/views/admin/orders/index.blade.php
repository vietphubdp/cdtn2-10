@extends('layouts.admin')

@section('title', 'Đơn hàng')

@section('content')
    <h1 class="h3 mb-4">Đơn hàng</h1>

    @php
        // Bộ lọc mã đơn / ngày được giữ lại khi bấm sang tab trạng thái khác
        $filters = array_filter(['code' => $code, 'from' => $from, 'to' => $to]);
        $hasFilter = $filters !== [];
    @endphp

    {{-- Tab trạng thái kèm số đơn (Hình 15) --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.orders.index', $filters) }}"
           @class(['btn btn-sm', 'btn-dark' => ! $status, 'btn-outline-secondary' => $status])>Tất cả ({{ $counts->sum() }})</a>
        @foreach (\App\Enums\OrderStatus::cases() as $case)
            <a href="{{ route('admin.orders.index', ['status' => $case->value] + $filters) }}"
               @class(['btn btn-sm', 'btn-dark' => $status === $case, 'btn-outline-secondary' => $status !== $case])>{{ $case->label() }} ({{ $counts[$case->value] ?? 0 }})</a>
        @endforeach
    </div>

    {{-- Lọc theo mã đơn và khoảng ngày đặt --}}
    <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 mb-3">
        @if ($status)
            <input type="hidden" name="status" value="{{ $status->value }}">
        @endif
        <div class="col-12 col-md">
            <input type="search" name="code" value="{{ $code }}" class="form-control" placeholder="Mã đơn (TS-...)" aria-label="Mã đơn">
        </div>
        <div class="col-6 col-md-auto">
            <input type="date" name="from" value="{{ $from }}" class="form-control" aria-label="Từ ngày" title="Từ ngày">
        </div>
        <div class="col-6 col-md-auto">
            <input type="date" name="to" value="{{ $to }}" class="form-control" aria-label="Đến ngày" title="Đến ngày">
        </div>
        <div class="col-12 col-md-auto d-flex gap-2">
            <button type="submit" class="btn btn-outline-dark">Lọc</button>
            @if ($hasFilter)
                <a href="{{ route('admin.orders.index', $status ? ['status' => $status->value] : []) }}" class="btn btn-outline-secondary">Bỏ lọc</a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th>
                        <th class="d-none d-md-table-cell">Khách hàng</th>
                        <th class="d-none d-sm-table-cell">Ngày đặt</th>
                        <th class="text-end">Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-dark text-nowrap">{{ $order->code }}</a>
                                {{-- Màn nhỏ ẩn cột Khách hàng/Ngày đặt nên hiện dưới mã đơn --}}
                                <div class="small text-secondary d-md-none">{{ $order->receiver_name }}</div>
                                <div class="small text-secondary d-sm-none text-nowrap">{{ $order->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $order->receiver_name }} · {{ $order->receiver_phone }}</td>
                            <td class="d-none d-sm-table-cell text-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end text-nowrap">@money($order->total)</td>
                            <td><span class="badge text-bg-{{ $order->status->color() }}">{{ $order->status->label() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                {{ $hasFilter || $status ? 'Không có đơn hàng phù hợp bộ lọc.' : 'Chưa có đơn hàng nào.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
@endsection
