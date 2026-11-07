<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * 1 admin + 2 khách + 1 khách đã bị khóa, mật khẩu đều là "password".
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Quản trị viên',
            'email' => 'admin@techshop.test',
            'phone' => '0901000001',
            'address' => '12 Phan Xích Long, P. 2, Q. Phú Nhuận, TP.HCM',
        ]);

        User::factory()->create([
            'name' => 'Nguyễn Văn An',
            'email' => 'khach@techshop.test',
            'phone' => '0912345678',
            'address' => '45 Nguyễn Thị Minh Khai, P. Bến Thành, Q. 1, TP.HCM',
        ]);

        User::factory()->create([
            'name' => 'Trần Thị Bình',
            'email' => 'khach2@techshop.test',
            'phone' => '0987654321',
            'address' => '120 Lê Văn Sỹ, P. 10, Q. Phú Nhuận, TP.HCM',
        ]);

        // Tài khoản đã bị khóa – dùng để thử "không đăng nhập được" (FR-14)
        User::factory()->locked()->create([
            'name' => 'Lê Văn Cường',
            'email' => 'locked@techshop.test',
            'phone' => '0933111222',
            'address' => '8 Võ Văn Tần, P. 6, Q. 3, TP.HCM',
        ]);
    }
}
