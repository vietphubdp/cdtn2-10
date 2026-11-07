<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * 13 đơn mẫu của 2 khách, đủ 5 trạng thái, rải trong khoảng 20 ngày gần đây.
     * Đơn cuối hoàn thành ngay hôm nay để dashboard có doanh thu hôm nay/tháng này khác 0.
     * Đơn được tạo qua OrderService giống khách đặt thật: lưu giá lúc mua, trừ kho,
     * đơn bị hủy được cộng lại kho (BR-04, BR-06).
     * Mỗi dòng: [email khách, [tên SP => số lượng], trạng thái cuối, đặt cách đây mấy ngày]
     */
    public function run(OrderService $orderService): void
    {
        $orders = [
            ['khach@techshop.test', ['Tai nghe Sony WH-1000XM5' => 1], OrderStatus::Completed, 20],
            ['khach2@techshop.test', ['Sạc dự phòng Anker 323 10000mAh' => 2, 'Cáp Ugreen USB-C 100W 2m bện dù' => 2], OrderStatus::Completed, 18],
            ['khach@techshop.test', ['Chuột Logitech MX Master 3S' => 1, 'Bàn phím Logitech MX Keys S' => 1], OrderStatus::Completed, 15],
            ['khach2@techshop.test', ['Loa bluetooth JBL Go 4' => 1], OrderStatus::Cancelled, 13],
            ['khach@techshop.test', ['Củ sạc Apple USB-C 20W' => 1, 'Cáp Apple USB-C to Lightning 1m' => 1], OrderStatus::Completed, 11],
            ['khach2@techshop.test', ['Tai nghe JBL Tune 520BT' => 1], OrderStatus::Shipping, 8],
            ['khach@techshop.test', ['Bàn phím cơ Akko 3068B Plus' => 1, 'Chuột Logitech G304 Lightspeed' => 1], OrderStatus::Shipping, 6],
            ['khach2@techshop.test', ['Loa bluetooth JBL Flip 6' => 1], OrderStatus::Confirmed, 4],
            ['khach@techshop.test', ['Sạc dự phòng Xiaomi 20000mAh 22.5W' => 1], OrderStatus::Cancelled, 3],
            ['khach@techshop.test', ['Tai nghe Apple AirPods 4' => 1], OrderStatus::Confirmed, 2],
            ['khach2@techshop.test', ['Chuột Logitech G102 Lightsync' => 1, 'Bàn phím cơ DareU EK87' => 1], OrderStatus::Pending, 1],
            ['khach@techshop.test', ['Loa bluetooth Sony SRS-XB100' => 2], OrderStatus::Pending, 0],
            ['khach2@techshop.test', ['Củ sạc Anker 511 Nano 3 30W' => 1, 'Cáp Anker PowerLine III USB-C to USB-C 1.8m' => 1], OrderStatus::Completed, 0],
        ];

        foreach ($orders as [$email, $items, $status, $daysAgo]) {
            $user = User::where('email', $email)->firstOrFail();

            // Đổi tên SP thành id: [product_id => số lượng]
            $quantities = [];
            foreach ($items as $name => $quantity) {
                $quantities[Product::where('name', $name)->value('id')] = $quantity;
            }

            $order = $orderService->placeOrder($user, [
                'receiver_name' => $user->name,
                'receiver_phone' => $user->phone,
                'shipping_address' => $user->address,
                'note' => null,
            ], $quantities);

            // Chuyển lần lượt từng bước đúng luồng BR-05 tới trạng thái cuối
            $steps = match ($status) {
                OrderStatus::Pending => [],
                OrderStatus::Confirmed => [OrderStatus::Confirmed],
                OrderStatus::Shipping => [OrderStatus::Confirmed, OrderStatus::Shipping],
                OrderStatus::Completed => [OrderStatus::Confirmed, OrderStatus::Shipping, OrderStatus::Completed],
                OrderStatus::Cancelled => [OrderStatus::Cancelled],
            };
            foreach ($steps as $step) {
                $order = $orderService->changeStatus($order, $step);
            }

            // Lùi ngày đặt để demo lọc theo khoảng ngày
            $order->created_at = now()->subDays($daysAgo);
            $order->save();
        }
    }
}
