<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Quản lý người dùng, khóa/mở khóa tài khoản (FR-14).
 */
class UserController extends Controller
{
    /**
     * Danh sách người dùng (Hình 17): tìm theo tên/email/SĐT, lọc đang hoạt động/bị khóa,
     * kèm số đơn của từng người; 20 người/trang.
     */
    public function index(Request $request)
    {
        // Tham số sai kiểu hoặc giá trị lạ coi như không lọc
        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $status = in_array($request->query('status'), ['active', 'locked'], true) ? $request->query('status') : '';

        $users = User::withCount('orders')
            // Gom 3 điều kiện OR trong 1 nhóm để không phá điều kiện lọc trạng thái phía sau
            ->when($q !== '', fn ($query) => $query->where(function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%')
                    ->orWhere('phone', 'like', '%'.$q.'%');
            }))
            ->when($status !== '', fn ($query) => $query->where('is_locked', $status === 'locked'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q', 'status'));
    }

    /**
     * Khóa ↔ mở khóa tài khoản. Không được khóa tài khoản quản trị (NFR-02).
     * Người đang đăng nhập bị khóa sẽ tự bị đăng xuất ở request kế tiếp (middleware not_locked).
     */
    public function toggleLock(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Không thể khóa tài khoản quản trị "'.$user->name.'".');
        }

        // is_locked không nằm trong $fillable nên gán trực tiếp
        $user->is_locked = ! $user->is_locked;
        $user->save();

        $message = $user->is_locked
            ? 'Đã khóa tài khoản "'.$user->name.'". Người này không thể đăng nhập nữa.'
            : 'Đã mở khóa tài khoản "'.$user->name.'".';

        return back()->with('success', $message);
    }
}
