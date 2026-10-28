@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">Đăng nhập</h1>

                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="email@gmail.com" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input">
                            <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Đăng nhập</button>
                    </form>

                    <p class="mt-3 mb-0 text-center">
                        Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
