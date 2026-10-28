<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fn (array $attributes) => Product::find($attributes['product_id'])->name,
            'price' => fn (array $attributes) => Product::find($attributes['product_id'])->final_price,
            'quantity' => $quantity,
            'line_total' => fn (array $attributes) => $attributes['price'] * $attributes['quantity'],
        ];
    }
}
