<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Phụ kiện công nghệ chính hãng') – TechShop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    @include('partials.header')
    @include('partials.nav')

    <main class="container py-4 flex-grow-1">
        @include('partials.flash')

        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
