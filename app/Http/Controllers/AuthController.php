<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Đăng ký, đăng nhập, đăng xuất tự viết bằng Auth::attempt (FR-01, FR-02).
 */
class AuthController extends Controller
{
    public function showRegister()
    {
        abort(501); // TODO [T-01]
    }

    public function register(Request $request)
    {
        abort(501); // TODO [T-02]
    }

    public function showLogin()
    {
        abort(501); // TODO [T-04]
    }

    public function login(Request $request)
    {
        abort(501); // TODO [T-04]
    }

    public function logout(Request $request)
    {
        abort(501); // TODO [T-04]
    }
}
