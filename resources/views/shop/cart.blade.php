@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
    <h1 class="h3 fw-bold mb-3">Giỏ hàng của bạn</h1>

    @if ($items->isEmpty())
        <div class="p-4 bg-white border rounded text-center">
            <p class="mb-3">Giỏ hàng của bạn đang trống.</p>
            <a href="{{ route('products.index') }}" class="btn btn-dark">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="bg-white border rounded">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="d-none d-sm-table-cell">Ảnh</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-end d-none d-sm-table-cell">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    @php($product = $item['product'])
                                    <tr>
                                        <td class="d-none d-sm-table-cell">
                                            @if ($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="56" height="56" class="rounded border object-fit-cover">
                                            @else
                                                <div class="rounded border bg-light text-secondary small d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">Ảnh</div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('products.show', $product->slug) }}" class="text-dark">{{ $product->name }}</a>
                                            {{-- Màn hình nhỏ ẩn cột ảnh, đơn giá → hiện đơn giá dưới tên --}}
                                            <div class="small text-secondary text-nowrap d-sm-none">@money($product->final_price)</div>
                                        </td>
                                        <td class="text-end text-nowrap d-none d-sm-table-cell">@money($product->final_price)</td>
                                        <td>
                                            {{-- Nút −/+ gửi số lượng mới; tối đa bằng tồn kho (BR-03) --}}
                                            <form method="POST" action="{{ route('cart.update', $product->id) }}" class="d-flex align-items-center justify-content-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" class="btn btn-sm btn-outline-secondary"
                                                        aria-label="Giảm số lượng" @disabled($item['quantity'] <= 1)>−</button>
                                                <span class="fw-semibold">{{ $item['quantity'] }}</span>
                                                <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="btn btn-sm btn-outline-secondary"
                                                        aria-label="Tăng số lượng" @disabled($item['quantity'] >= $product->stock)>+</button>
                                            </form>
                                        </td>
                                        <td class="text-end text-nowrap fw-semibold">@money($item['line_total'])</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('cart.remove', $product->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger text-nowrap">× Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="p-3 bg-white border rounded">
                    <h2 class="h6 fw-bold text-uppercase">Tóm tắt</h2>
                    <div class="d-flex justify-content-between">
                        <span>Tạm tính:</span>
                        <strong>@money($subtotal)</strong>
                    </div>
                    <p class="small text-secondary mt-2">Mã giảm giá và phí được tính ở bước thanh toán</p>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Tổng:</span>
                        <strong class="fs-5">@money($subtotal)</strong>
                    </div>
                    {{-- Khách chưa đăng nhập sẽ được chuyển tới trang đăng nhập rồi quay lại thanh toán --}}
                    <a href="{{ route('checkout.index') }}" class="btn btn-dark w-100">Tiến hành đặt hàng</a>
                </div>
            </div>
        </div>
    @endif
@endsection
