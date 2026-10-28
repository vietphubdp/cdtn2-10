<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Đăng ký, đăng nhập, đăng xuất tự viết bằng Auth::attempt (FR-01, FR-02).
 */
class AuthController extends Controller
{
    public function showRegister()
    {
        $this->rememberPreviousUrl();

        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        // role mặc định customer (cột DB); password tự băm bcrypt nhờ cast 'hashed'
        $user = User::create($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Đăng ký thành công. Chào mừng bạn đến với TechShop!');
    }

    public function showLogin()
    {
        $this->rememberPreviousUrl();

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->validated(), $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])
                ->onlyInput('email');
        }

        // Tài khoản bị khóa không được đăng nhập (NFR-02)
        if (Auth::user()->is_locked) {
            Auth::logout();

            return back()
                ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ shop.')
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        // FR-02: admin → /admin, khách → quay lại trang trước
        if (Auth::user()->isAdmin()) {
            $request->session()->forget('url.intended');

            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Bạn đã đăng xuất.');
    }

    /**
     * Ghi nhớ trang khách đang xem để đăng nhập/đăng ký xong quay lại đúng trang đó.
     * Nếu khách bị chuyển tới đây từ trang cần đăng nhập thì Laravel đã tự ghi nhớ rồi.
     */
    private function rememberPreviousUrl(): void
    {
        $previous = url()->previous();

        if (session()->has('url.intended')
            || $previous === url()->current()
            || in_array($previous, [route('login'), route('register')], true)) {
            return;
        }

        session(['url.intended' => $previous]);
    }
}
