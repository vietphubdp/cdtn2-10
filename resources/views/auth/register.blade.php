@extends('layouts.app')

@section('title', 'Đăng ký tài khoản')

@section('content')
    <section class="login-panel register-panel" aria-labelledby="register-title">
        <div class="login-visual" aria-hidden="true">
            <img src="{{ asset('images/login-headphones.jpg') }}" alt="">
        </div>
        <div class="login-content">
            <h1 id="register-title" class="mb-4">Đăng ký tài khoản</h1>

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" autocomplete="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" autocomplete="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="tel" id="phone" name="phone" autocomplete="tel" value="{{ old('phone') }}"
                           class="form-control @error('phone') is-invalid @enderror"
                           placeholder="09xxxxxxxx" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" autocomplete="new-password" id="password" name="password" aria-describedby="password-hint"
                               class="form-control @error('password') is-invalid @enderror" required>
                        <div id="password-hint" class="form-text">Ít nhất 8 ký tự.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label for="password_confirmation" class="form-label">Nhập lại mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" autocomplete="new-password" id="password_confirmation" name="password_confirmation"
                               class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark login-submit w-100">Tạo tài khoản @include('partials.icon', ['name' => 'arrow-right', 'size' => 18])</button>
            </form>

            <p class="mt-3 mb-0 text-center">
                Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
            </p>
        </div>
    </section>
@endsection
