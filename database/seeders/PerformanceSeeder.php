<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PerformanceSeeder extends Seeder
{
    /**
     * Thêm 500 SP ngẫu nhiên để đo tốc độ trang (NFR-01).
     * Không chạy mặc định: php artisan db:seed --class=PerformanceSeeder
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        // recycle: SP gắn vào các danh mục có sẵn thay vì tạo danh mục mới
        Product::factory()->count(400)->recycle($categories)->create();
        Product::factory()->count(100)->onSale()->recycle($categories)->create();
    }
}
