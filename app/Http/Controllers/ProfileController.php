<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordRequest;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Hash;

/**
 * Hồ sơ cá nhân và đổi mật khẩu (FR-15).
 */
class ProfileController extends Controller
{
    public function edit()
    {
        return view('account.profile', ['user' => auth()->user()]);
    }

    public function update(ProfileRequest $request)
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.edit')->with('success', 'Đã cập nhật hồ sơ.');
    }

    public function password(PasswordRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->to(route('profile.edit').'#doi-mat-khau')->with('success', 'Đã đổi mật khẩu.');
    }
}
