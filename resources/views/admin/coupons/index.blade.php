@extends('layouts.admin')

@section('title', 'Mã giảm giá')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">Mã giảm giá</h1>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-dark">+ Thêm mã</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th>
                        <th>Mức giảm</th>
                        <th class="text-end d-none d-md-table-cell">Đơn tối thiểu</th>
                        <th class="text-center d-none d-sm-table-cell">Đã dùng</th>
                        <th class="d-none d-lg-table-cell">Thời hạn</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        @php
                            [$statusLabel, $statusColor] = $coupon->statusBadge();
                        @endphp
                        <tr>
                            <td class="fw-semibold text-nowrap">{{ $coupon->code }}</td>
                            <td>{{ $coupon->describe() }}</td>
                            <td class="text-end text-nowrap d-none d-md-table-cell">@money($coupon->min_order)</td>
                            <td class="text-center text-nowrap d-none d-sm-table-cell">
                                {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}
                            </td>
                            <td class="small text-nowrap d-none d-lg-table-cell">
                                {{ $coupon->starts_at->format('d/m/Y') }} – {{ $coupon->expires_at->format('d/m/Y') }}
                            </td>
                            <td><span class="badge text-bg-{{ $statusColor }}">{{ $statusLabel }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">Sửa</a>

                                {{-- Mã đã dùng trong đơn chỉ được tắt (bỏ tick "Đang bật" ở form sửa) --}}
                                @unless ($coupon->orders_exists)
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="d-inline"
                                          onsubmit="return confirm(@js('Xóa mã giảm giá "'.$coupon->code.'"?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Chưa có mã giảm giá nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="small text-secondary mt-2 mb-0">Nút "Xóa" chỉ có với mã chưa dùng trong đơn nào; mã đã dùng chỉ có thể tắt.</p>

    <div class="mt-3">
        {{ $coupons->links('pagination::bootstrap-5') }}
    </div>
@endsection
