# TechShop – Website bán phụ kiện công nghệ

Đồ án môn Chuyên đề tốt nghiệp 2 – Trường Đại học Văn Lang. Trang web phục vụ khách hàng có thể tìm phụ kiện, đặt hàng thanh toán khi nhận hàng (COD) và theo dõi tiến trình giao hàng. Trang quản trị dùng để vận hành cửa hàng và theo dõi doanh thu.

Giảng viên hướng dẫn: ThS. Nguyễn Văn Trung.

Nhóm thực hiện: Võ Phạm Việt Phú, Vongthirath Sithiphak, Phoutthavong Khanxay.

## Chạy thử bằng Docker

Cần Docker Desktop đang chạy ở chế độ Linux containers, hoặc Docker Engine có Compose. Mở terminal tại thư mục chứa [compose.yaml](compose.yaml) và chạy:

```bash
docker compose up -d --build
```

Lệnh này build giao diện, khởi động web và MySQL, rồi tự chạy migration. Khi [trang chủ](http://localhost:8080) mở được, chạy lệnh sau **một lần cho database demo mới** để tạo tài khoản, 6 danh mục, 60 sản phẩm cùng đơn hàng và mã giảm giá mẫu:

```bash
docker compose exec web php artisan db:seed --force
```

| Vai trò       | Email                 | Mật khẩu demo | Trang truy cập                               |
| ------------- | --------------------- | ------------- | -------------------------------------------- |
| Khách hàng    | `khach@techshop.test` | `password`    | [Đăng nhập](http://localhost:8080/dang-nhap) |
| Quản trị viên | `admin@techshop.test` | `password`    | [Quản trị](http://localhost:8080/admin)      |

Dùng `docker compose stop` khi kết thúc và `docker compose start` để mở lại. Dữ liệu MySQL và ảnh tải lên được giữ trong Docker volumes. Mặc định trang demo chỉ truy cập được từ máy chạy Docker, tại cổng `8080`.

**Ảnh sản phẩm:** Seeder để trống ảnh của 60 sản phẩm. Có thể tải ảnh tại **Quản trị → Sản phẩm → Sửa**. Để khôi phục cửa hàng với đúng bộ ảnh đã có, cần bản sao MySQL và thư mục `storage/app/public/products/` tương ứng. Khi khôi phục vào database mới, dùng bản sao dữ liệu thay cho bước seed.

## Chức năng

| Khu vực          | Chức năng                                                                                                                                         |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| Cửa hàng         | Duyệt danh mục, tìm kiếm và lọc sản phẩm, xem giá khuyến mãi, thêm vào giỏ và cập nhật số lượng.                                                  |
| Tài khoản khách  | Đăng ký, cập nhật hồ sơ, đổi mật khẩu, nhập địa chỉ giao hàng, áp mã giảm giá và đặt COD.                                                         |
| Sau khi đặt hàng | Xem mã đơn, nhận email xác nhận, theo dõi trạng thái, hủy đơn đang chờ xác nhận và đánh giá sản phẩm sau khi đơn hoàn thành.                      |
| Quản trị         | Quản lý danh mục, sản phẩm và ảnh, xử lý đơn hàng, quản lý mã giảm giá, khóa hoặc mở khóa tài khoản, xem thống kê doanh thu và sản phẩm bán chạy. |

**Luồng demo:** Đăng nhập bằng tài khoản khách, thêm một sản phẩm vào giỏ, chọn **Xem giỏ hàng** và đặt COD. Từ màn hình đặt hàng thành công, chọn **Theo dõi đơn hàng**. Mở trang quản trị bằng trình duyệt khác, cập nhật trạng thái đơn rồi tải lại trang chi tiết phía khách để xem kết quả.

## Cài trực tiếp để phát triển

Ứng dụng dùng Laravel 13, PHP 8.3, MySQL 8, Blade, Bootstrap 5.3 và Vite. Máy phát triển cần Composer 2, Node.js 22.12 trở lên thuộc dòng 22 và các extension PHP `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `curl`, `zip`, `intl`. Bộ kiểm thử PHPUnit dùng thêm `pdo_sqlite`.

1. Lấy mã nguồn và cài thư viện:

    ```bash
    git clone https://github.com/vietphubdp/cdtn2-10.git
    cd cdtn2-10
    composer install
    npm ci
    ```

2. Sao chép `.env.example` thành `.env`. Dùng `Copy-Item .env.example .env` trên PowerShell hoặc `cp .env.example .env` trên Linux/macOS. Tạo database MySQL trống tên `techshop` với charset `utf8mb4`, rồi điền `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` và `DB_PASSWORD` trong `.env`.

3. Khởi tạo và chạy ứng dụng:

    ```bash
    php artisan key:generate
    php artisan migrate --seed
    php artisan storage:link
    npm run build
    php artisan serve
    ```

    Mở [TechShop trên localhost:8000](http://localhost:8000). Khi chỉnh CSS hoặc JavaScript, có thể mở terminal thứ hai và chạy `npm run dev` để Vite cập nhật giao diện.

## Email xác nhận đơn hàng

Sau khi lưu đơn thành công, TechShop gửi xác nhận tới email của tài khoản đặt hàng. Chế độ demo mặc định dùng `MAIL_MAILER=log`. Đặt một đơn mới rồi tìm mã đơn trong:

- **Docker:** chạy `docker compose logs web`.
- **Cài trực tiếp:** mở `storage/logs/laravel.log`.

**Gửi từ Gmail của shop khi deploy.** Bật xác minh hai bước cho tài khoản gửi và tạo mật khẩu ứng dụng Google nếu tài khoản hỗ trợ. Điền cấu hình sau vào `.env` trên VPS (cùng thư mục với `compose.yaml`), thay địa chỉ và mật khẩu mẫu bằng giá trị thật:

```dotenv
SHOP_URL=https://shop.example.com
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=dia-chi-shop@gmail.com
MAIL_PASSWORD="mat-khau-ung-dung-google"
MAIL_FROM_ADDRESS=dia-chi-shop@gmail.com
MAIL_FROM_NAME=TechShop
```

Chạy `docker compose up -d web` để áp dụng. Với bản cài trực tiếp, đặt tên miền vào `APP_URL` rồi chạy `php artisan config:clear`. Giữ mật khẩu thật trong môi trường deploy. Để kiểm tra, đăng nhập bằng tài khoản khách có hộp thư truy cập được, đặt một đơn mới rồi đối chiếu mã đơn trong email. Nếu thiếu thư, xem log ứng dụng để kiểm tra lỗi SMTP.

Để thử bằng Mailtrap, dùng địa chỉ máy chủ, cổng và thông tin đăng nhập của sandbox, với `MAIL_SCHEME=smtp` khi dùng STARTTLS.

## Kiểm thử

Từ thư mục mã nguồn trên máy phát triển, chạy:

```bash
php artisan test
```

Bộ test dùng SQLite trong bộ nhớ theo [phpunit.xml](phpunit.xml). Các tình huống kiểm tra gồm phân quyền, tồn kho, mã giảm giá, đặt và hủy đơn, email xác nhận, đánh giá và thống kê doanh thu.
