@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3 mb-4">Dashboard</h1>

    {{-- Thẻ số liệu chính (Hình 12) – doanh thu chỉ tính đơn Hoàn thành (BR-08) --}}
    @php
        $cards = [
            ['Doanh thu hôm nay', $revenueToday, route('admin.orders.index', ['status' => 'completed']), true],
            ['Doanh thu tháng này', $revenueThisMonth, route('admin.orders.index', ['status' => 'completed']), true],
            ['Đơn chờ xác nhận', $pendingCount, route('admin.orders.index', ['status' => 'pending']), false],
            ['Khách hàng', $customerCount, route('admin.users.index'), false],
        ];
    @endphp

    <div class="row g-3 mb-3">
        @foreach ($cards as [$label, $value, $url, $isMoney])
            <div class="col-12 col-sm-6 col-xxl-3">
                <a href="{{ $url }}" class="stat-card card h-100 text-decoration-none shadow-sm">
                    <div class="card-body">
                        <div class="text-secondary">{{ $label }}</div>
                        <div class="fs-2 fw-bold text-dark">@if ($isMoney) @money($value) @else {{ $value }} @endif</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Số đơn theo 5 trạng thái, bấm vào để xem danh sách đơn đã lọc --}}
    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-5 g-2 mb-4">
        @foreach (\App\Enums\OrderStatus::cases() as $case)
            <div class="col">
                <a href="{{ route('admin.orders.index', ['status' => $case->value]) }}"
                   class="card h-100 text-decoration-none shadow-sm">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center gap-2">
                        <span class="badge text-bg-{{ $case->color() }}">{{ $case->label() }}</span>
                        <span class="fs-5 fw-bold text-dark">{{ $statusCounts[$case->value] }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- Biểu đồ cột doanh thu vẽ bằng div: chiều cao cột = % so với cột cao nhất --}}
        <div class="col-12 col-xl-7">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h6 fw-bold text-uppercase mb-0">
                            {{ $range === 'month' ? 'Doanh thu 12 tháng năm '.now()->year : 'Doanh thu 7 ngày gần nhất' }}
                        </h2>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Chọn khoảng thời gian">
                            <a href="{{ route('admin.dashboard') }}"
                               @class(['btn', 'btn-dark' => $range === 'day', 'btn-outline-secondary' => $range !== 'day'])>7 ngày</a>
                            <a href="{{ route('admin.dashboard', ['range' => 'month']) }}"
                               @class(['btn', 'btn-dark' => $range === 'month', 'btn-outline-secondary' => $range !== 'month'])>12 tháng</a>
                        </div>
                    </div>

                    @php($max = max($chart))

                    <div class="d-flex gap-1 gap-sm-2" style="height: 220px">
                        @foreach ($chart as $label => $total)
                            <div class="flex-fill d-flex flex-column text-center" style="min-width: 0"
                                 title="{{ $label }}: @money($total)">
                                <div class="flex-grow-1 d-flex align-items-end border-bottom">
                                    <div class="w-100 rounded-top"
                                         style="height: {{ $max > 0 ? round($total / $max * 100) : 0 }}%; background-color: var(--ts-accent)"></div>
                                </div>
                                <div class="small text-secondary text-truncate mt-1">{{ $label }}</div>
                                <span class="visually-hidden">@money($total)</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-secondary small mt-3">
                        Tổng: <strong class="text-dark">@money(array_sum($chart))</strong>
                        · rê chuột vào cột để xem số tiền
                    </div>
                </div>
            </div>
        </div>

        {{-- Top 5 sản phẩm bán chạy (theo số lượng trong đơn Hoàn thành) --}}
        <div class="col-12 col-xl-5">
            <div class="card shadow-sm h-100">
                <div class="card-body pb-0">
                    <h2 class="h6 fw-bold text-uppercase mb-0">Top 5 bán chạy</h2>
                </div>
                @if ($topProducts === [])
                    <div class="card-body text-secondary">Chưa có đơn hàng hoàn thành.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Đã bán</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topProducts as $index => $product)
                                    <tr>
                                        <td>{{ $index + 1 }}. {{ $product['name'] }}</td>
                                        <td class="text-end">{{ $product['quantity'] }}</td>
                                        <td class="text-end text-nowrap">@money($product['revenue'])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
