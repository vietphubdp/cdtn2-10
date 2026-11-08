{{-- Email dùng CSS inline vì trình đọc mail không tải được app.css --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Xác nhận đơn hàng {{ $order->code }}</title>
</head>
<body style="margin: 0; padding: 24px; background: #f4f5f7; font-family: Arial, Helvetica, sans-serif; color: #212529;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #dee2e6; border-radius: 8px; padding: 24px;">
        <h1 style="margin: 0 0 16px; font-size: 20px;">{{ config('app.name') }} – Đặt hàng thành công</h1>

        <p style="margin: 0 0 12px;">Xin chào {{ $order->receiver_name }},</p>
        <p style="margin: 0 0 16px;">
            Cảm ơn bạn đã đặt hàng. Đơn <strong>{{ $order->code }}</strong> đang <strong>{{ $order->status->label() }}</strong>,
            cửa hàng sẽ liên hệ trước khi giao.
        </p>

        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">Sản phẩm</th>
                    <th style="text-align: center; padding: 8px; border-bottom: 1px solid #dee2e6;">SL</th>
                    <th style="text-align: right; padding: 8px; border-bottom: 1px solid #dee2e6;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">
                            {{ $item->product_name }}
                            <div style="color: #6c757d; font-size: 12px;">@money($item->price)</div>
                        </td>
                        <td style="text-align: center; padding: 8px; border-bottom: 1px solid #dee2e6;">{{ $item->quantity }}</td>
                        <td style="text-align: right; padding: 8px; border-bottom: 1px solid #dee2e6; white-space: nowrap;">@money($item->line_total)</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; padding: 6px 8px;">Tạm tính</td>
                    <td style="text-align: right; padding: 6px 8px; white-space: nowrap;">@money($order->subtotal)</td>
                </tr>
                @if ($order->discount > 0)
                    <tr>
                        <td colspan="2" style="text-align: right; padding: 6px 8px;">Giảm giá{{ $order->coupon ? ' ('.$order->coupon->code.')' : '' }}</td>
                        <td style="text-align: right; padding: 6px 8px; white-space: nowrap;">−@money($order->discount)</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="2" style="text-align: right; padding: 6px 8px; font-weight: bold;">Tổng thanh toán</td>
                    <td style="text-align: right; padding: 6px 8px; font-weight: bold; white-space: nowrap;">@money($order->total)</td>
                </tr>
            </tfoot>
        </table>

        <p style="margin: 16px 0 4px;"><strong>Thanh toán:</strong> khi nhận hàng (COD)</p>
        <p style="margin: 0 0 20px;">
            <strong>Giao tới:</strong> {{ $order->receiver_name }} · {{ $order->receiver_phone }} · {{ $order->shipping_address }}
        </p>

        <p style="margin: 0 0 20px;">
            <a href="{{ route('orders.show', $order->code) }}"
               style="display: inline-block; background: #212529; color: #ffffff; text-decoration: none; padding: 10px 16px; border-radius: 6px;">
                Xem chi tiết đơn hàng
            </a>
        </p>

        <p style="margin: 0; color: #6c757d; font-size: 12px;">Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>
