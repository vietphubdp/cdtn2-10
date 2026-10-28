<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

/**
 * Quản lý người dùng, khóa/mở khóa tài khoản (FR-14).
 */
class UserController extends Controller
{
    public function index()
    {
        abort(501); // TODO Sprint 3 (FR-14)
    }

    public function toggleLock(User $user)
    {
        abort(501); // TODO Sprint 3 (FR-14)
    }
}
