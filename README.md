# Website bán phụ kiện công nghệ

Đồ án môn Chuyên đề tốt nghiệp 2 – Trường Đại học Văn Lang.
GVHD: ThS. Nguyễn Văn Trung.

## Chức năng chính

- **Khách hàng:** đăng ký, đăng nhập; xem, tìm kiếm, lọc sản phẩm; giỏ hàng; đặt hàng (COD), áp mã giảm giá, nhận email xác nhận; theo dõi và hủy đơn; đánh giá sản phẩm.
- **Quản trị:** quản lý danh mục, sản phẩm (kèm ảnh), đơn hàng, người dùng, mã giảm giá; thống kê doanh thu.

## Công nghệ

- PHP 8.3, Laravel 13
- Blade, Bootstrap 5.3, Vite
- MySQL 8
- PHPUnit

## Yêu cầu

- PHP ≥ 8.3 (extension: `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `curl`, `zip`, `intl`)
- Composer 2
- Node.js ≥ 20.19
- MySQL 8

## Cài đặt

```bash
git clone https://github.com/vietphubdp/cdtn2-10.git
cd cdtn2-10

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Tạo CSDL `techshop` (utf8mb4) trong MySQL, sửa `DB_USERNAME` / `DB_PASSWORD` trong `.env`, sau đó:

```bash
php artisan migrate --seed
php artisan storage:link
composer run dev
```

Truy cập http://localhost:8000.

Tài khoản quản trị mẫu: `admin@techshop.test` / `password`.

## Email xác nhận đơn hàng

Mặc định `.env` dùng `MAIL_MAILER=log`: mail không gửi ra ngoài mà được ghi vào `storage/logs/laravel.log`. Đặt một đơn hàng rồi mở file này, tìm mã đơn (ví dụ `TS-0014`) hoặc dòng `To: <email khách>` để xem nội dung mail (dòng `Subject` bị mã hóa vì có chữ tiếng Việt).

Tùy chọn: muốn xem mail trong hộp thư thử nghiệm, tạo tài khoản Mailtrap rồi sửa `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<username Mailtrap>
MAIL_PASSWORD=<password Mailtrap>
```

Sau khi sửa `.env` chạy `php artisan config:clear`.

## Kiểm thử

```bash
php artisan test
```

## Nhóm thực hiện

- Võ Phạm Việt Phú
- Sithiphak
- Khanxay
