@extends('layouts.app')

@section('title', 'Sản phẩm')

@section('content')
    <h1 class="h4 mb-3">Tất cả sản phẩm ({{ $products->total() }} sản phẩm)</h1>

    @if ($products->isEmpty())
        <div class="p-5 bg-white border rounded text-center text-secondary">
            Không tìm thấy sản phẩm phù hợp
        </div>
    @else
        {{-- Lưới 12 SP/trang; màn hình nhỏ 2 cột --}}
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
            @foreach ($products as $product)
                <div class="col">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
