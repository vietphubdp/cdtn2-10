<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email xác nhận đơn hàng gửi cho khách sau khi đặt thành công (FR-18).
 * Gửi ngay (không qua hàng đợi) để demo không cần chạy queue worker.
 */
class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items', 'coupon');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận đơn hàng '.$this->order->code.' – '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-placed');
    }
}
