<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tài khoản bị khóa đang đăng nhập sẽ bị đăng xuất ngay (NFR-02).
 */
class EnsureNotLocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_locked) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị khóa.');
        }

        return $next($request);
    }
}
