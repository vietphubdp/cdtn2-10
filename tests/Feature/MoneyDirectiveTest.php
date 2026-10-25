<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class MoneyDirectiveTest extends TestCase
{
    public function test_money_directive_formats_vnd(): void
    {
        $this->assertSame('1.500.000đ', Blade::render('@money(1500000)'));
        $this->assertSame('0đ', Blade::render('@money($v)', ['v' => 0]));
    }
}
