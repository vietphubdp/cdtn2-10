<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tài khoản tối thiểu để đăng nhập thử; dữ liệu demo đầy đủ bổ sung ở C-11
        User::factory()->admin()->create([
            'name' => 'Quản trị viên',
            'email' => 'admin@techshop.test',
        ]);

        User::factory()->create([
            'name' => 'Khách hàng mẫu',
            'email' => 'khach@techshop.test',
        ]);
    }
}
