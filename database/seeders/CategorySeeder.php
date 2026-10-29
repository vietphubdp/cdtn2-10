<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * 6 danh mục của cửa hàng; slug sinh từ tên (Tai nghe → tai-nghe).
     */
    public function run(): void
    {
        $categories = [
            'Tai nghe' => 'Tai nghe chụp tai, tai nghe không dây, chống ồn.',
            'Sạc dự phòng' => 'Pin sạc dự phòng nhiều dung lượng, hỗ trợ sạc nhanh.',
            'Củ sạc và cáp' => 'Củ sạc nhanh GaN, cáp USB-C, Lightning chính hãng.',
            'Chuột' => 'Chuột văn phòng, chuột gaming, chuột không dây.',
            'Bàn phím' => 'Bàn phím cơ, bàn phím không dây cho máy tính và máy tính bảng.',
            'Loa bluetooth' => 'Loa di động, chống nước, pin lâu.',
        ];

        foreach ($categories as $name => $description) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
            ]);
        }
    }
}
