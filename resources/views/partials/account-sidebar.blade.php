{{-- Menu "Tài khoản" bên trái các trang tài khoản (Hình 9, 10, 11) --}}
<div class="p-3 bg-white border rounded">
    <h2 class="h6 fw-bold text-uppercase">Tài khoản</h2>
    <nav class="nav flex-column">
        <a href="{{ route('profile.edit') }}" @class(['nav-link px-0 text-dark', 'fw-bold' => request()->routeIs('profile.*')])>Hồ sơ cá nhân</a>
        <a href="{{ route('orders.index') }}" @class(['nav-link px-0 text-dark', 'fw-bold' => request()->routeIs('orders.*')])>Đơn hàng của tôi</a>
        <a href="{{ route('profile.edit') }}#doi-mat-khau" class="nav-link px-0 text-dark">Đổi mật khẩu</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link px-0 text-dark">Đăng xuất</button>
        </form>
    </nav>
</div>
