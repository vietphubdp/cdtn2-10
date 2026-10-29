<header class="bg-white border-bottom border-2">
    <div class="container py-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <a href="{{ route('home') }}" class="d-inline-block border border-2 border-dark px-3 py-1 fs-5 fw-bold text-dark text-decoration-none">
                    TECHSHOP
                </a>
            </div>

            {{-- Ô tìm kiếm: gửi ?q= sang trang danh sách SP (FR-04). Màn hình nhỏ xuống dòng riêng --}}
            <div class="col-12 col-md order-last order-md-0">
                <form action="{{ route('products.index') }}" method="GET" role="search">
                    <div class="input-group">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                               placeholder="Tìm sản phẩm theo tên..." aria-label="Tìm sản phẩm">
                        <button type="submit" class="btn btn-outline-secondary">Tìm</button>
                    </div>
                </form>
            </div>

            <div class="col-auto ms-auto d-flex gap-2">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Trang quản trị</a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">Đơn hàng của tôi</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Tài khoản</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">Đăng nhập / Đăng ký</a>
                @endauth

                <a href="{{ route('cart.index') }}" class="btn btn-dark text-nowrap">
                    Giỏ hàng <span class="badge text-bg-light">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </div>
</header>
