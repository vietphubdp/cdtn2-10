{{-- Thông báo sau khi thao tác: controller gọi ->with('success', '...') hoặc ->with('error', '...') --}}
@if (session('cart_added') && session('success'))
    <div class="cart-notice alert alert-dismissible show" role="status">
        @include('partials.icon', ['name' => 'check-circle', 'size' => 24])
        <div>
            <p class="mb-2">{{ session('success') }}</p>
            <a href="{{ route('cart.index') }}" class="btn btn-dark btn-sm">Xem giỏ hàng</a>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng thông báo"></button>
    </div>
@elseif (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
@endif
