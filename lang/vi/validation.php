<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thông báo lỗi validate (tiếng Việt)
    |--------------------------------------------------------------------------
    |
    | Dùng khi APP_LOCALE=vi. :attribute được thay bằng tên trường trong
    | mảng 'attributes' ở cuối file.
    |
    */

    'accepted' => ':Attribute phải được chấp nhận.',
    'accepted_if' => ':Attribute phải được chấp nhận khi :other là :value.',
    'active_url' => ':Attribute không phải là URL hợp lệ.',
    'after' => ':Attribute phải là ngày sau :date.',
    'after_or_equal' => ':Attribute phải là ngày sau hoặc bằng :date.',
    'alpha' => ':Attribute chỉ được chứa chữ cái.',
    'alpha_dash' => ':Attribute chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới.',
    'alpha_num' => ':Attribute chỉ được chứa chữ cái và số.',
    'array' => ':Attribute phải là một mảng.',
    'ascii' => ':Attribute chỉ được chứa ký tự ASCII.',
    'before' => ':Attribute phải là ngày trước :date.',
    'before_or_equal' => ':Attribute phải là ngày trước hoặc bằng :date.',
    'between' => [
        'array' => ':Attribute phải có từ :min đến :max phần tử.',
        'file' => ':Attribute phải có dung lượng từ :min đến :max KB.',
        'numeric' => ':Attribute phải nằm trong khoảng :min đến :max.',
        'string' => ':Attribute phải có từ :min đến :max ký tự.',
    ],
    'boolean' => ':Attribute phải là đúng hoặc sai.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'current_password' => 'Mật khẩu hiện tại không đúng.',
    'date' => ':Attribute không phải là ngày hợp lệ.',
    'date_equals' => ':Attribute phải là ngày :date.',
    'date_format' => ':Attribute không đúng định dạng :format.',
    'decimal' => ':Attribute phải có :decimal chữ số thập phân.',
    'declined' => ':Attribute phải bị từ chối.',
    'different' => ':Attribute và :other phải khác nhau.',
    'digits' => ':Attribute phải gồm :digits chữ số.',
    'digits_between' => ':Attribute phải có từ :min đến :max chữ số.',
    'dimensions' => ':Attribute có kích thước ảnh không hợp lệ.',
    'distinct' => ':Attribute bị trùng giá trị.',
    'email' => ':Attribute phải là địa chỉ email hợp lệ.',
    'ends_with' => ':Attribute phải kết thúc bằng một trong các giá trị: :values.',
    'enum' => ':Attribute đã chọn không hợp lệ.',
    'exists' => ':Attribute đã chọn không tồn tại.',
    'file' => ':Attribute phải là một tệp.',
    'filled' => ':Attribute không được để trống.',
    'gt' => [
        'array' => ':Attribute phải có nhiều hơn :value phần tử.',
        'file' => ':Attribute phải lớn hơn :value KB.',
        'numeric' => ':Attribute phải lớn hơn :value.',
        'string' => ':Attribute phải dài hơn :value ký tự.',
    ],
    'gte' => [
        'array' => ':Attribute phải có ít nhất :value phần tử.',
        'file' => ':Attribute phải lớn hơn hoặc bằng :value KB.',
        'numeric' => ':Attribute phải lớn hơn hoặc bằng :value.',
        'string' => ':Attribute phải có ít nhất :value ký tự.',
    ],
    'image' => ':Attribute phải là hình ảnh.',
    'in' => ':Attribute đã chọn không hợp lệ.',
    'in_array' => ':Attribute không có trong :other.',
    'integer' => ':Attribute phải là số nguyên.',
    'ip' => ':Attribute phải là địa chỉ IP hợp lệ.',
    'json' => ':Attribute phải là chuỗi JSON hợp lệ.',
    'lowercase' => ':Attribute phải viết thường.',
    'lt' => [
        'array' => ':Attribute phải có ít hơn :value phần tử.',
        'file' => ':Attribute phải nhỏ hơn :value KB.',
        'numeric' => ':Attribute phải nhỏ hơn :value.',
        'string' => ':Attribute phải ngắn hơn :value ký tự.',
    ],
    'lte' => [
        'array' => ':Attribute không được có nhiều hơn :value phần tử.',
        'file' => ':Attribute phải nhỏ hơn hoặc bằng :value KB.',
        'numeric' => ':Attribute phải nhỏ hơn hoặc bằng :value.',
        'string' => ':Attribute không được dài quá :value ký tự.',
    ],
    'max' => [
        'array' => ':Attribute không được có nhiều hơn :max phần tử.',
        'file' => ':Attribute không được lớn hơn :max KB.',
        'numeric' => ':Attribute không được lớn hơn :max.',
        'string' => ':Attribute không được dài quá :max ký tự.',
    ],
    'mimes' => ':Attribute phải là tệp có định dạng: :values.',
    'mimetypes' => ':Attribute phải là tệp có định dạng: :values.',
    'min' => [
        'array' => ':Attribute phải có ít nhất :min phần tử.',
        'file' => ':Attribute phải có dung lượng ít nhất :min KB.',
        'numeric' => ':Attribute phải lớn hơn hoặc bằng :min.',
        'string' => ':Attribute phải có ít nhất :min ký tự.',
    ],
    'missing' => ':Attribute không được có mặt.',
    'multiple_of' => ':Attribute phải là bội số của :value.',
    'not_in' => ':Attribute đã chọn không hợp lệ.',
    'not_regex' => ':Attribute không đúng định dạng.',
    'numeric' => ':Attribute phải là số.',
    'password' => [
        'letters' => ':Attribute phải chứa ít nhất một chữ cái.',
        'mixed' => ':Attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers' => ':Attribute phải chứa ít nhất một chữ số.',
        'symbols' => ':Attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':Attribute đã bị lộ trong một vụ rò rỉ dữ liệu, vui lòng chọn :attribute khác.',
    ],
    'present' => ':Attribute phải có mặt.',
    'prohibited' => ':Attribute không được phép nhập.',
    'regex' => ':Attribute không đúng định dạng.',
    'required' => 'Vui lòng nhập :attribute.',
    'required_if' => 'Vui lòng nhập :attribute khi :other là :value.',
    'required_unless' => 'Vui lòng nhập :attribute trừ khi :other là :values.',
    'required_with' => 'Vui lòng nhập :attribute khi có :values.',
    'required_with_all' => 'Vui lòng nhập :attribute khi có :values.',
    'required_without' => 'Vui lòng nhập :attribute khi không có :values.',
    'required_without_all' => 'Vui lòng nhập :attribute khi không có :values.',
    'same' => ':Attribute và :other phải giống nhau.',
    'size' => [
        'array' => ':Attribute phải có :size phần tử.',
        'file' => ':Attribute phải có dung lượng :size KB.',
        'numeric' => ':Attribute phải bằng :size.',
        'string' => ':Attribute phải có :size ký tự.',
    ],
    'starts_with' => ':Attribute phải bắt đầu bằng một trong các giá trị: :values.',
    'string' => ':Attribute phải là chuỗi ký tự.',
    'timezone' => ':Attribute phải là múi giờ hợp lệ.',
    'unique' => ':Attribute đã được sử dụng.',
    'uploaded' => 'Tải lên :attribute thất bại.',
    'uppercase' => ':Attribute phải viết hoa.',
    'url' => ':Attribute phải là URL hợp lệ.',
    'uuid' => ':Attribute phải là UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Thông báo riêng cho từng trường
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'phone' => [
            'regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0.',
        ],
        'receiver_phone' => [
            'regex' => 'Số điện thoại người nhận phải gồm 10 chữ số và bắt đầu bằng 0.',
        ],
        'sale_price' => [
            'lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tên hiển thị của các trường (CLAUDE.md mục 7)
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        // Tài khoản
        'name' => 'họ tên',
        'email' => 'email',
        'phone' => 'số điện thoại',
        'password' => 'mật khẩu',
        'password_confirmation' => 'xác nhận mật khẩu',
        'current_password' => 'mật khẩu hiện tại',
        'address' => 'địa chỉ',

        // Giỏ hàng
        'product_id' => 'sản phẩm',
        'quantity' => 'số lượng',

        // Thanh toán
        'receiver_name' => 'tên người nhận',
        'receiver_phone' => 'số điện thoại người nhận',
        'shipping_address' => 'địa chỉ giao hàng',
        'note' => 'ghi chú',

        // Sản phẩm, danh mục (admin)
        'category_id' => 'danh mục',
        'price' => 'giá',
        'sale_price' => 'giá khuyến mãi',
        'stock' => 'tồn kho',
        'image' => 'ảnh',
        'description' => 'mô tả',
        'is_active' => 'trạng thái hiển thị',

        // Đánh giá
        'rating' => 'số sao',
        'comment' => 'nhận xét',

        // Mã giảm giá
        'code' => 'mã giảm giá',
        'type' => 'loại giảm giá',
        'value' => 'giá trị',
        'min_order' => 'giá trị đơn tối thiểu',
        'max_discount' => 'mức giảm tối đa',
        'usage_limit' => 'số lượt sử dụng',
        'starts_at' => 'ngày bắt đầu',
        'expires_at' => 'ngày hết hạn',
    ],

];
