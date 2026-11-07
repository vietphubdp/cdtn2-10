<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tạo đơn của khách có chứa SP, với trạng thái cho trước.
     */
    private function buy(User $user, Product $product, OrderStatus $status): Order
    {
        $order = Order::factory()->status($status)->create(['user_id' => $user->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

        return $order;
    }

    public function test_customer_with_completed_order_can_review_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = $this->buy($user, $product, OrderStatus::Completed);

        $this->actingAs($user)
            ->post(route('reviews.store', $product->slug), ['rating' => 4, 'comment' => 'Dùng rất ổn.'])
            ->assertRedirect(route('products.show', $product->slug).'#danh-gia')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Dùng rất ổn.',
        ]);
    }

    public function test_comment_is_optional(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->buy($user, $product, OrderStatus::Completed);

        $this->actingAs($user)
            ->post(route('reviews.store', $product->slug), ['rating' => 5])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'rating' => 5, 'comment' => null]);
    }

    // TC-25: chưa mua SP → bị từ chối
    public function test_customer_who_has_not_bought_product_cannot_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('reviews.store', $product->slug), ['rating' => 5])
            ->assertRedirect(route('products.show', $product->slug).'#danh-gia')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_order_not_completed_does_not_allow_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->buy($user, $product, OrderStatus::Shipping);
        $this->buy($user, $product, OrderStatus::Cancelled);

        $this->actingAs($user)
            ->post(route('reviews.store', $product->slug), ['rating' => 5])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 0);
    }

    // TC-26: đánh giá lần 2 cùng SP → bị từ chối
    public function test_customer_cannot_review_same_product_twice(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->buy($user, $product, OrderStatus::Completed);

        $this->actingAs($user)->post(route('reviews.store', $product->slug), ['rating' => 5]);

        $this->actingAs($user)
            ->post(route('reviews.store', $product->slug), ['rating' => 1, 'comment' => 'Lần hai'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseMissing('reviews', ['comment' => 'Lần hai']);
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->buy($user, $product, OrderStatus::Completed);

        foreach ([0, 6, 'abc', null] as $rating) {
            $this->actingAs($user)
                ->post(route('reviews.store', $product->slug), ['rating' => $rating])
                ->assertSessionHasErrors('rating');
        }

        $this->actingAs($user)
            ->post(route('reviews.store', $product->slug), ['rating' => 5, 'comment' => str_repeat('a', 1001)])
            ->assertSessionHasErrors('comment');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $product = Product::factory()->create();

        $this->post(route('reviews.store', $product->slug), ['rating' => 5])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_hidden_product_cannot_be_reviewed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->hidden()->create();
        $this->buy($user, $product, OrderStatus::Completed);

        $this->actingAs($user)
            ->post(route('reviews.store', $product->slug), ['rating' => 5])
            ->assertNotFound();
    }

    public function test_form_is_shown_only_to_eligible_customer(): void
    {
        $product = Product::factory()->create();
        $buyer = User::factory()->create();
        $this->buy($buyer, $product, OrderStatus::Completed);
        $stranger = User::factory()->create();

        // Khách chưa đăng nhập: link đăng nhập, không có form
        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Đăng nhập để đánh giá')
            ->assertDontSee('Gửi đánh giá');

        // Khách chưa mua: dòng ghi chú, không có form
        $this->actingAs($stranger)
            ->get(route('products.show', $product->slug))
            ->assertSee('Chỉ khách đã mua và nhận hàng thành công')
            ->assertDontSee('Gửi đánh giá');

        // Khách đã mua: có form
        $this->actingAs($buyer)
            ->get(route('products.show', $product->slug))
            ->assertSee('Gửi đánh giá')
            ->assertSee(route('reviews.store', $product->slug), false)
            ->assertSee('name="rating"', false);
    }

    public function test_reviewed_customer_sees_message_instead_of_form(): void
    {
        $review = Review::factory()->create();

        $this->actingAs($review->user)
            ->get(route('products.show', $review->product->slug))
            ->assertSee('Bạn đã đánh giá sản phẩm này')
            ->assertDontSee('Gửi đánh giá');
    }

    public function test_new_review_appears_first_and_average_is_updated(): void
    {
        $product = Product::factory()->create();
        Review::factory()->for($product)->create(['rating' => 2, 'comment' => 'Đánh giá cũ']);

        $user = User::factory()->create();
        $this->buy($user, $product, OrderStatus::Completed);

        $this->actingAs($user)
            ->post(route('reviews.store', $product->slug), ['rating' => 5, 'comment' => 'Đánh giá mới']);

        // Trung bình (2 + 5) / 2 = 3,5; đánh giá mới nằm trên cùng
        $this->get(route('products.show', $product->slug))
            ->assertSee('id="danh-gia"', false)
            ->assertSee('3,5/5')
            ->assertSee('(2 đánh giá)')
            ->assertSeeInOrder(['Đánh giá mới', 'Đánh giá cũ']);
    }

    public function test_completed_order_detail_has_review_link(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $completed = $this->buy($user, $product, OrderStatus::Completed);
        $pending = $this->buy($user, $product, OrderStatus::Pending);
        $link = route('products.show', $product->slug).'#danh-gia';

        $this->actingAs($user)
            ->get(route('orders.show', $completed->code))
            ->assertOk()
            ->assertSee($link, false);

        $this->actingAs($user)
            ->get(route('orders.show', $pending->code))
            ->assertOk()
            ->assertDontSee($link, false);
    }
}
