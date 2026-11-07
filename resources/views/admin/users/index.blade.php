@extends('layouts.admin')

@section('title', 'Người dùng')

@section('content')
    <h1 class="h3 mb-4">Người dùng</h1>

    @php
        $hasFilter = $q !== '' || $status !== '';
    @endphp

    {{-- Tìm theo tên / email / SĐT, lọc trạng thái tài khoản (Hình 17) --}}
    <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md">
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Tìm theo tên, email, số điện thoại..." aria-label="Tìm người dùng">
        </div>
        <div class="col-12 col-sm-6 col-md-auto">
            <select name="status" class="form-select" aria-label="Trạng thái">
                <option value="">Tất cả trạng thái</option>
                <option value="active" @selected($status === 'active')>Đang hoạt động</option>
                <option value="locked" @selected($status === 'locked')>Đã khóa</option>
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-auto d-flex gap-2">
            <button type="submit" class="btn btn-outline-dark">Lọc</button>
            @if ($hasFilter)
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Bỏ lọc</a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Họ tên</th>
                        <th class="d-none d-md-table-cell">Email</th>
                        <th class="d-none d-sm-table-cell">Số điện thoại</th>
                        <th class="text-center">Số đơn</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                {{-- Màn nhỏ ẩn cột Email/SĐT nên hiện dưới tên --}}
                                <div class="small text-secondary d-md-none text-break">{{ $user->email }}</div>
                                <div class="small text-secondary d-sm-none">{{ $user->phone }}</div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $user->email }}</td>
                            <td class="d-none d-sm-table-cell text-nowrap">{{ $user->phone }}</td>
                            <td class="text-center">{{ $user->orders_count }}</td>
                            <td class="text-nowrap">
                                @if ($user->isAdmin())
                                    <span class="badge text-bg-primary">Quản trị</span>
                                @elseif ($user->is_locked)
                                    <span class="badge text-bg-danger">Đã khóa</span>
                                @else
                                    <span class="badge text-bg-success">Hoạt động</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                {{-- Tài khoản quản trị không có nút khóa (NFR-02) --}}
                                @unless ($user->isAdmin())
                                    <form method="POST" action="{{ route('admin.users.lock', $user) }}" class="d-inline"
                                          onsubmit="return confirm(@js(($user->is_locked ? 'Mở khóa' : 'Khóa').' tài khoản "'.$user->name.'"?'))">
                                        @csrf
                                        @method('PATCH')
                                        @if ($user->is_locked)
                                            <button type="submit" class="btn btn-sm btn-outline-success">Mở khóa</button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Khóa</button>
                                        @endif
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                {{ $hasFilter ? 'Không có người dùng phù hợp bộ lọc.' : 'Chưa có người dùng nào.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="small text-secondary mt-2 mb-0">Tài khoản bị khóa không đăng nhập được; nếu đang đăng nhập sẽ bị đăng xuất ở lần thao tác kế tiếp.</p>

    <div class="mt-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
@endsection
