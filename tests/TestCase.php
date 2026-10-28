<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test không cần file build của Vite (máy chưa chạy npm run build vẫn test được)
        $this->withoutVite();
    }
}
