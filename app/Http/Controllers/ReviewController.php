<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Product;

/**
 * Đánh giá SP đã mua (FR-12, BR-07).
 */
class ReviewController extends Controller
{
    public function store(ReviewRequest $request, string $slug)
    {
        // Chỉ đánh giá SP đang bán: SP ẩn hoặc slug sai → 404 (BR-02)
        $product = Product::active()->where('slug', $slug)->firstOrFail();
        $user = $request->user();

        $backToReviews = redirect()->to(route('products.show', $product->slug).'#danh-gia');

        // BR-07: mỗi khách chỉ đánh giá 1 lần cho 1 SP
        if ($user->hasReviewed($product)) {
            return $backToReviews->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        // BR-07: phải có đơn "Hoàn thành" chứa SP này mới được đánh giá
        $orderId = $user->completedOrderIdFor($product);
        if ($orderId === null) {
            return $backToReviews->with('error', 'Bạn chỉ có thể đánh giá sản phẩm đã mua và nhận hàng thành công.');
        }

        $product->reviews()->create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
        ]);

        return $backToReviews->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }
}
