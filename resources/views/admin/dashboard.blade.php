@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3 mb-4">Dashboard</h1>

    @php
        $cards = [
            ['Danh mục', $categoryCount, route('admin.categories.index')],
            ['Sản phẩm', $productCount, route('admin.products.index')],
            ['Đơn hàng', $orderCount, route('admin.orders.index')],
            ['Khách hàng', $customerCount, route('admin.users.index')],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($cards as [$label, $count, $url])
            <div class="col-6 col-xl-3">
                <a href="{{ $url }}" class="card h-100 text-center text-decoration-none shadow-sm">
                    <div class="card-body">
                        <div class="text-secondary">{{ $label }}</div>
                        <div class="fs-2 fw-bold text-dark">{{ $count }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="alert alert-secondary mb-0">
        Thống kê doanh thu theo ngày, số đơn theo trạng thái và top 5 sản phẩm bán chạy sẽ hiển thị tại đây.
    </div>
@endsection
