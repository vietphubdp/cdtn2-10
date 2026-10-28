<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_allowed_transitions_follow_br05(): void
    {
        $this->assertTrue(OrderStatus::Pending->canTransitionTo(OrderStatus::Confirmed));
        $this->assertTrue(OrderStatus::Pending->canTransitionTo(OrderStatus::Cancelled));
        $this->assertTrue(OrderStatus::Confirmed->canTransitionTo(OrderStatus::Shipping));
        $this->assertTrue(OrderStatus::Confirmed->canTransitionTo(OrderStatus::Cancelled));
        $this->assertTrue(OrderStatus::Shipping->canTransitionTo(OrderStatus::Completed));
    }

    public function test_invalid_transitions_are_blocked(): void
    {
        $this->assertFalse(OrderStatus::Pending->canTransitionTo(OrderStatus::Shipping));
        $this->assertFalse(OrderStatus::Shipping->canTransitionTo(OrderStatus::Cancelled));
        $this->assertFalse(OrderStatus::Completed->canTransitionTo(OrderStatus::Cancelled));
        $this->assertSame([], OrderStatus::Cancelled->nextStatuses());
    }

    public function test_customer_can_cancel_only_pending_order(): void
    {
        $this->assertTrue(OrderStatus::Pending->canBeCancelledByCustomer());
        $this->assertFalse(OrderStatus::Confirmed->canBeCancelledByCustomer());
    }

    public function test_labels_are_vietnamese(): void
    {
        $this->assertSame('Chờ xác nhận', OrderStatus::Pending->label());
        $this->assertSame('Đã hủy', OrderStatus::Cancelled->label());
    }
}
