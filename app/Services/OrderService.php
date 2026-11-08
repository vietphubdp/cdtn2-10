<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private CouponService $coupons = new CouponService) {}

    /**
     * Đặt hàng COD (BR-04, BR-10). Cả đơn, các dòng đơn và trừ kho nằm trong 1 transaction:
     * thiếu hàng ở bất kỳ SP nào thì không có gì được ghi.
     *
     * @param  array{receiver_name: string, receiver_phone: string, shipping_address: string, note?: ?string}  $shipping
     * @param  array<int, int>  $quantities  [product_id => số lượng], lấy từ CartService::quantities()
     * @param  ?string  $couponCode  mã giảm giá khách đã áp (BR-09), kiểm tra lại trong transaction
     */
    public function placeOrder(User $user, array $shipping, array $quantities, ?string $couponCode = null): Order
    {
        if ($quantities === []) {
            throw new BusinessException('Giỏ hàng đang trống.');
        }

        return DB::transaction(function () use ($user, $shipping, $quantities, $couponCode) {
            // Khóa các dòng SP tới hết transaction để 2 khách không cùng mua 1 món cuối
            $products = Product::whereIn('id', array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product || ! $product->is_active) {
                    throw new BusinessException('Có sản phẩm trong giỏ không còn được bán, vui lòng kiểm tra lại giỏ hàng.');
                }
                if ($quantity > $product->stock) {
                    throw new BusinessException('Sản phẩm "'.$product->name.'" chỉ còn '.$product->stock.' trong kho, vui lòng cập nhật giỏ hàng.');
                }

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $product->final_price * $quantity,
                ];
            }

            $subtotal = array_sum(array_column($lines, 'line_total'));

            // Mã có thể vừa hết lượt/hết hạn sau khi khách áp → kiểm tra lại, khóa dòng mã tới hết transaction
            $coupon = $couponCode !== null ? $this->coupons->findUsable($couponCode, $subtotal, lock: true) : null;
            $discount = $coupon ? $this->coupons->discountFor($coupon, $subtotal) : 0;

            $order = Order::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon?->id,
                'receiver_name' => $shipping['receiver_name'],
                'receiver_phone' => $shipping['receiver_phone'],
                'shipping_address' => $shipping['shipping_address'],
                'note' => $shipping['note'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $subtotal - $discount,
                'payment_method' => 'cod',
                'status' => OrderStatus::Pending,
            ]);

            // Mã đơn cần id nên gán ngay sau insert
            $order->update(['code' => Order::makeCode($order->id)]);

            foreach ($lines as $line) {
                // Lưu tên và giá lúc mua: sau này admin đổi giá/tên SP thì đơn cũ không đổi
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'price' => $line['product']->final_price,
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);

                $line['product']->decrement('stock', $line['quantity']);
            }

            $coupon?->increment('used_count');

            return $order;
        });
    }

    /**
     * Hủy đơn và cộng lại tồn kho (BR-06). Dùng cho cả khách và admin;
     * controller phía khách tự kiểm tra thêm canBeCancelledByCustomer() (chỉ đơn pending).
     */
    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            // Đọc lại trạng thái mới nhất và khóa đơn để không hoàn kho 2 lần
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if (! $order->status->canTransitionTo(OrderStatus::Cancelled)) {
                throw new BusinessException('Không thể hủy đơn '.$order->code.' ở trạng thái "'.$order->status->label().'".');
            }

            $order->update(['status' => OrderStatus::Cancelled]);

            foreach ($order->items as $item) {
                Product::whereKey($item->product_id)->increment('stock', $item->quantity);
            }

            return $order;
        });
    }

    /**
     * Admin đổi trạng thái đơn theo BR-05; chọn "Đã hủy" thì đi qua cancel() để hoàn kho.
     */
    public function changeStatus(Order $order, OrderStatus $status): Order
    {
        if ($status === OrderStatus::Cancelled) {
            return $this->cancel($order);
        }

        return DB::transaction(function () use ($order, $status) {
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if (! $order->status->canTransitionTo($status)) {
                throw new BusinessException('Không thể chuyển đơn '.$order->code.' từ "'.$order->status->label().'" sang "'.$status->label().'".');
            }

            $order->update(['status' => $status]);

            return $order;
        });
    }
}
