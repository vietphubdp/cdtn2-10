<nav id="shopMenu" class="site-nav offcanvas-md offcanvas-start" tabindex="-1" aria-label="Điều hướng cửa hàng">
    <div class="offcanvas-header">
        <span class="fw-bold fs-5">Menu cửa hàng</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#shopMenu" aria-label="Đóng menu"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container">
            <ul class="nav flex-nowrap text-nowrap">
                <li class="nav-item">
                    <a href="{{ route('home') }}"
                       @if (request()->routeIs('home')) aria-current="page" @endif
                       class="nav-link px-2 me-2 {{ request()->routeIs('home') ? 'fw-bold text-dark' : 'text-secondary' }}">
                        Trang chủ
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a href="{{ route('products.index') }}" class="nav-link px-2 {{ request()->routeIs('products.index') && ! request('category') ? 'fw-bold' : '' }}">Tất cả sản phẩm</a>
                </li>
                @foreach ($navCategories as $category)
                    <li class="nav-item">
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                           @if (request('category') === $category->slug) aria-current="page" @endif
                           class="nav-link px-2 me-2 {{ request('category') === $category->slug ? 'fw-bold text-dark' : 'text-secondary' }}">
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
