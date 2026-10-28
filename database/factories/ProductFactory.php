<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Tai nghe', 'Sạc dự phòng', 'Cáp sạc', 'Chuột', 'Bàn phím', 'Loa'])
            .' '.fake()->randomElement(['Anker', 'Baseus', 'Logitech', 'Sony', 'Xiaomi', 'JBL', 'Ugreen'])
            .' '.strtoupper(fake()->bothify('??-###'));

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'price' => fake()->numberBetween(5, 500) * 10000,
            'sale_price' => null,
            'stock' => fake()->numberBetween(1, 100),
            'image' => null,
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Giá khuyến mãi giảm 10–30% so với giá gốc, làm tròn nghìn.
     */
    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_price' => (int) (round($attributes['price'] * fake()->numberBetween(70, 90) / 100 / 1000) * 1000),
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
