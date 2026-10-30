@extends('layouts.app')

@section('title', 'Không tìm thấy trang')

@section('content')
    <div class="p-5 bg-white border rounded text-center">
        <p class="display-4 fw-bold mb-2">404</p>
        <h1 class="h4 mb-3">Không tìm thấy trang hoặc sản phẩm</h1>
        <p class="text-secondary mb-4">Sản phẩm có thể đã ngừng bán hoặc đường dẫn không đúng.</p>
        <a href="{{ route('products.index') }}" class="btn btn-dark">Xem sản phẩm khác</a>
    </div>
@endsection
