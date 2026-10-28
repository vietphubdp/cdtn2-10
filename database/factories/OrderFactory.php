<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(5, 300) * 10000;

        return [
            'user_id' => User::factory(),
            'receiver_name' => fake()->name(),
            'receiver_phone' => '0'.fake()->numerify('9########'),
            'shipping_address' => fake()->address(),
            'note' => null,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'payment_method' => 'cod',
            'status' => OrderStatus::Pending,
        ];
    }

    /**
     * Gán mã TS-xxxx sau khi tạo, giống cách OrderService làm (BR-10).
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            if ($order->code === null) {
                $order->update(['code' => Order::makeCode($order->id)]);
            }
        });
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
