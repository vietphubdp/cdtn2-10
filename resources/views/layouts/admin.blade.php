<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quản trị') – TechShop Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    {{-- Thanh trên cùng chỉ hiện ở màn hình nhỏ: nút ☰ mở sidebar (NFR-03) --}}
    <nav class="navbar navbar-dark bg-dark d-lg-none px-3">
        <span class="navbar-brand fw-bold mb-0">TECHSHOP ADMIN</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar"
                aria-controls="adminSidebar" aria-label="Mở menu quản trị">
            <span class="navbar-toggler-icon"></span>
        </button>
    </nav>

    <div class="d-lg-flex min-vh-100">
        {{-- Màn hình lớn: sidebar cố định bên trái. Màn hình nhỏ: sidebar thu gọn, trượt ra khi bấm ☰ --}}
        <div class="admin-sidebar">
            <aside class="offcanvas-lg offcanvas-start text-bg-dark" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
                @include('partials.admin-sidebar')
            </aside>
        </div>

        <main class="flex-grow-1 p-3 p-md-4 min-w-0">
            @include('partials.flash')

            @yield('content')
        </main>
    </div>
</body>
</html>
