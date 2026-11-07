@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
    <div class="row g-4">
        <div class="col-lg-3">
            @include('partials.account-sidebar')
        </div>

        <div class="col-lg-9">
            <h1 class="h3 fw-bold mb-3">Hồ sơ cá nhân</h1>

            {{-- Thông tin cá nhân --}}
            <div class="p-3 p-md-4 bg-white border rounded mb-4">
                <form method="POST" action="{{ route('profile.update') }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" value="{{ $user->email }}" class="form-control" disabled>
                        <div class="form-text">Email dùng để đăng nhập, không thay đổi được.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="09xxxxxxxx" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ giao hàng mặc định</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}"
                               class="form-control @error('address') is-invalid @enderror">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-dark">Lưu thay đổi</button>
                </form>
            </div>

            {{-- Đổi mật khẩu --}}
            <div id="doi-mat-khau" class="p-3 p-md-4 bg-white border rounded">
                <h2 class="h5 fw-bold mb-3">Đổi mật khẩu</h2>

                <form method="POST" action="{{ route('profile.password') }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <input type="password" id="current_password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Mật khẩu mới <span class="text-danger">*</span> (≥ 8 ký tự)</label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Nhập lại mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-dark">Đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
@endsection
