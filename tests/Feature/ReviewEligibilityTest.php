<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewEligibilityTest extends TestCase
{
    use RefreshDatabase;

    private function buy(User $user, Product $product, OrderStatus $status): Order
    {
        $order = Order::factory()->status($status)->create(['user_id' => $user->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

        return $order;
    }

    public function test_customer_without_any_order_cannot_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->assertNull($user->completedOrderIdFor($product));
        $this->assertFalse($user->canReview($product));
    }

    public function test_orders_that_are_not_completed_do_not_count(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        foreach ([OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Shipping, OrderStatus::Cancelled] as $status) {
            $this->buy($user, $product, $status);
        }

        $this->assertNull($user->completedOrderIdFor($product));
        $this->assertFalse($user->canReview($product));
    }

    public function test_completed_order_containing_product_allows_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = $this->buy($user, $product, OrderStatus::Completed);

        $this->assertSame($order->id, $user->completedOrderIdFor($product));
        $this->assertFalse($user->hasReviewed($product));
        $this->assertTrue($user->canReview($product));
    }

    public function test_completed_order_of_another_product_or_customer_does_not_count(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create();

        $this->buy($user, Product::factory()->create(), OrderStatus::Completed);
        $this->buy($other, $product, OrderStatus::Completed);

        $this->assertNull($user->completedOrderIdFor($product));
        $this->assertFalse($user->canReview($product));
    }

    public function test_latest_completed_order_is_returned_when_bought_many_times(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->buy($user, $product, OrderStatus::Completed);
        $latest = $this->buy($user, $product, OrderStatus::Completed);

        $this->assertSame($latest->id, $user->completedOrderIdFor($product));
    }

    public function test_customer_can_review_only_once_per_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = $this->buy($user, $product, OrderStatus::Completed);

        Review::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'order_id' => $order->id]);

        $this->assertTrue($user->hasReviewed($product));
        $this->assertFalse($user->canReview($product));
        // Đánh giá SP khác vẫn không bị ảnh hưởng
        $this->assertFalse($user->hasReviewed(Product::factory()->create()));
    }

    public function test_review_factory_creates_review_that_satisfies_br07(): void
    {
        $review = Review::factory()->create();

        $this->assertSame($review->order_id, $review->user->completedOrderIdFor($review->product));
        $this->assertTrue($review->user->hasReviewed($review->product));
        $this->assertContains($review->rating, range(1, 5));
    }

    public function test_database_rejects_second_review_of_same_product(): void
    {
        $review = Review::factory()->create();

        $this->expectException(QueryException::class);

        Review::factory()->create([
            'user_id' => $review->user_id,
            'product_id' => $review->product_id,
            'order_id' => $review->order_id,
        ]);
    }
}
