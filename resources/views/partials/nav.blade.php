{{-- Thanh danh mục: bấm danh mục → /san-pham?category=<slug> (FR-03). Màn hình nhỏ thì vuốt ngang --}}
<nav class="bg-white border-bottom" aria-label="Danh mục sản phẩm">
    <div class="container">
        <ul class="nav flex-nowrap overflow-x-auto text-nowrap">
            <li class="nav-item">
                <a href="{{ route('home') }}"
                   class="nav-link px-2 me-2 {{ request()->routeIs('home') ? 'fw-bold text-dark' : 'text-secondary' }}">
                    Trang chủ
                </a>
            </li>
            @foreach ($navCategories as $category)
                <li class="nav-item">
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                       class="nav-link px-2 me-2 {{ request('category') === $category->slug ? 'fw-bold text-dark' : 'text-secondary' }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
