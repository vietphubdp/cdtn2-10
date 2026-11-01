<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lỗi vi phạm quy tắc nghiệp vụ (hết hàng, sai trạng thái đơn…).
 * Message viết tiếng Việt để controller hiển thị thẳng: back()->with('error', $e->getMessage()).
 */
class BusinessException extends RuntimeException {}
