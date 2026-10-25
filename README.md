# TechShop – Website bán phụ kiện công nghệ

Đồ án môn Chuyên đề tốt nghiệp 2 – Trường Đại học Văn Lang.
GVHD: ThS. Nguyễn Văn Trung.

## Chức năng chính

- **Khách hàng:** đăng ký, đăng nhập; xem, tìm kiếm, lọc sản phẩm; giỏ hàng; đặt hàng (COD); theo dõi và hủy đơn; đánh giá sản phẩm.
- **Quản trị:** quản lý danh mục, sản phẩm (kèm ảnh), đơn hàng, người dùng; thống kê doanh thu.

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

## Kiểm thử

```bash
php artisan test
```

## Nhóm thực hiện

- Võ Phạm Việt Phú
- Sithiphak
- Khanxay
