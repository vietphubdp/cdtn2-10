@php
    // Mục menu: [tên route, chữ hiển thị, nhóm route để tô đậm mục đang mở]
    $menu = [
        ['admin.dashboard', 'Dashboard', 'admin.dashboard'],
        ['admin.categories.index', 'Danh mục', 'admin.categories.*'],
        ['admin.products.index', 'Sản phẩm', 'admin.products.*'],
        ['admin.orders.index', 'Đơn hàng', 'admin.orders.*'],
        ['admin.users.index', 'Người dùng', 'admin.users.*'],
        ['admin.coupons.index', 'Mã giảm giá', 'admin.coupons.*'],
    ];
@endphp

<div class="offcanvas-header border-bottom border-secondary">
    <h2 class="offcanvas-title h5 fw-bold mb-0" id="adminSidebarLabel">TECHSHOP ADMIN</h2>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Đóng"></button>
</div>

<div class="offcanvas-body d-flex flex-column p-3 w-100">
    <div class="d-none d-lg-block fs-5 fw-bold mb-1">TECHSHOP ADMIN</div>
    <div class="small text-white-50 mb-3">Xin chào, {{ auth()->user()->name }}</div>

    <ul class="nav flex-column">
        @foreach ($menu as [$route, $label, $pattern])
            <li class="nav-item border-bottom border-secondary">
                {{-- Mã giảm giá (Could – FR-16) chưa mở route thì hiện mờ, chưa bấm được --}}
                @if (Route::has($route))
                    <a href="{{ route($route) }}"
                       @class(['nav-link px-0', 'text-white fw-bold' => request()->routeIs($pattern), 'text-white-50' => ! request()->routeIs($pattern)])
                       @if (request()->routeIs($pattern)) aria-current="page" @endif>
                        {{ $label }}
                    </a>
                @else
                    <span class="nav-link px-0 disabled text-secondary">{{ $label }} (sắp có)</span>
                @endif
            </li>
        @endforeach

        <li class="nav-item border-bottom border-secondary">
            <a href="{{ route('home') }}" class="nav-link px-0 text-white-50">Xem trang khách</a>
        </li>
        <li class="nav-item border-bottom border-secondary">
            {{-- Đăng xuất bằng form POST có @csrf, không dùng link GET --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link px-0 text-white-50 bg-transparent border-0">Đăng xuất</button>
            </form>
        </li>
    </ul>
</div>
