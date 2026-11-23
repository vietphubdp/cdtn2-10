@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
    <section class="login-panel" aria-labelledby="login-title">
        <div class="login-visual" aria-hidden="true">
            <img src="{{ asset('images/login-headphones.jpg') }}" alt="">
        </div>
        <div class="login-content">
            <h1 id="login-title">Đăng nhập</h1>
            <p class="login-intro">Đăng nhập để theo dõi đơn hàng của bạn.</p>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="ban@example.com" autocomplete="username" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" id="remember" name="remember" value="1" @checked(old('remember')) class="form-check-input">
                    <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
                </div>

                <button type="submit" class="btn btn-dark login-submit w-100">Đăng nhập @include('partials.icon', ['name' => 'arrow-right', 'size' => 18])</button>
            </form>

            <p class="mt-3 mb-0 text-center">
                Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a>
            </p>
        </div>
    </section>
@endsection
