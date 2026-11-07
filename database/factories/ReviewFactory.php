<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Mặc định tạo luôn 1 đơn "Hoàn thành" của khách có chứa SP, để đánh giá hợp lệ theo BR-07.
     * Truyền sẵn order_id nếu đã có đơn.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'order_id' => function (array $attributes) {
                $order = Order::factory()
                    ->status(OrderStatus::Completed)
                    ->create(['user_id' => $attributes['user_id']]);

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $attributes['product_id'],
                ]);

                return $order->id;
            },
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
