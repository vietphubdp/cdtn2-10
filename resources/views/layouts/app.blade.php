<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TechShop') – TechShop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    {{-- Layout tạm, sẽ thay bằng header/nav/footer đầy đủ [T-07] --}}
    <nav class="navbar bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">TECHSHOP</a>
            <div class="d-flex gap-2">
                @auth
                    <span class="navbar-text">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Đăng xuất</button>
                    </form>
                @else
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('login') }}">Đăng nhập / Đăng ký</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @foreach (['success', 'error'] as $type)
            @if (session($type))
                <div class="alert alert-{{ $type === 'error' ? 'danger' : 'success' }}">{{ session($type) }}</div>
            @endif
        @endforeach

        @yield('content')
    </main>
</body>
</html>
