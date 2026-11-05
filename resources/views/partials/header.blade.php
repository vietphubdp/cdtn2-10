{{-- Dải thông báo trên cùng: chỉ hiện với khách chưa đăng nhập --}}
@guest
    <div class="announce-bar text-center small py-2 px-3">
        Tạo tài khoản để đặt hàng và theo dõi đơn dễ dàng.
        <a href="{{ route('register') }}" class="fw-semibold">Đăng ký ngay</a>
    </div>
@endguest

<header class="site-header bg-white">
    <div class="container py-3">
        <div class="row g-2 g-md-4 align-items-center">
            <div class="col-auto">
                <a href="{{ route('home') }}" class="site-logo text-decoration-none">CDTN2-10</a>
            </div>

            {{-- Ô tìm kiếm: gửi ?q= sang trang danh sách SP (FR-04). Màn hình nhỏ xuống dòng riêng --}}
            <div class="col-12 col-md order-last order-md-0">
                <form action="{{ route('products.index') }}" method="GET" role="search" class="site-search">
                    <span class="site-search-icon">@include('partials.icon', ['name' => 'search'])</span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                           placeholder="Tìm sản phẩm theo tên..." aria-label="Tìm sản phẩm">
                    <button type="submit" class="btn btn-dark">Tìm</button>
                </form>
            </div>

            <div class="col-auto ms-auto d-flex align-items-center gap-2">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary header-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @include('partials.icon', ['name' => 'user'])
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
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
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary header-btn" aria-label="Đăng nhập / Đăng ký">
                        @include('partials.icon', ['name' => 'user'])
                        <span class="d-none d-sm-inline">Đăng nhập / Đăng ký</span>
                    </a>
                @endauth

                <a href="{{ route('cart.index') }}" class="btn btn-dark header-btn text-nowrap">
                    @include('partials.icon', ['name' => 'cart'])
                    Giỏ hàng <span class="badge text-bg-light">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </div>
</header>
