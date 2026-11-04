<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Phụ kiện công nghệ chính hãng')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="shop-body d-flex flex-column min-vh-100 bg-light">
    @include('partials.header')
    @include('partials.nav')

    <main class="container py-4 flex-grow-1">
        @include('partials.flash')

        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
