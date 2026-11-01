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
    /**
     * Đặt hàng COD (BR-04, BR-10). Cả đơn, các dòng đơn và trừ kho nằm trong 1 transaction:
     * thiếu hàng ở bất kỳ SP nào thì không có gì được ghi.
     *
     * @param  array{receiver_name: string, receiver_phone: string, shipping_address: string, note?: ?string}  $shipping
     * @param  array<int, int>  $quantities  [product_id => số lượng], lấy từ CartService::quantities()
     */
    public function placeOrder(User $user, array $shipping, array $quantities): Order
    {
        if ($quantities === []) {
            throw new BusinessException('Giỏ hàng đang trống.');
        }

        return DB::transaction(function () use ($user, $shipping, $quantities) {
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

            $order = Order::create([
                'user_id' => $user->id,
                'receiver_name' => $shipping['receiver_name'],
                'receiver_phone' => $shipping['receiver_phone'],
                'shipping_address' => $shipping['shipping_address'],
                'note' => $shipping['note'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
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

            return $order;
        });
    }
}
