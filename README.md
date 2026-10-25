# TechShop – Website bán phụ kiện công nghệ

Đồ án môn Chuyên đề tốt nghiệp 2 – ĐH Văn Lang (GVHD: ThS. Nguyễn Văn Trung).
Nhóm: Phú (Tài khoản & Đặt hàng), Sithiphak (Quản trị), Khanxay (Cửa hàng & Giỏ hàng).

Công nghệ: Laravel 13, Blade + Bootstrap 5.3 (Vite), MySQL 8.

Trước khi làm việc, đọc `CLAUDE.md` (quy định chung – nhận từ Phú, đặt ở thư mục gốc repo, **không commit**) và `PLAN.md` (kế hoạch từng lượt + nhật ký bàn giao).

## 1. Yêu cầu máy

| Phần mềm | Phiên bản | Ghi chú |
|---|---|---|
| PHP | ≥ 8.3 | Bật extension trong `php.ini`: `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `curl`, `zip`, `intl` |
| Composer | 2.x | `composer -V` |
| Node.js | ≥ 20.19 | `node -v` |
| MySQL | 8.x | XAMPP/Laragon/MySQL Installer đều được |
| Git | bất kỳ | Cấu hình `git config --global user.name` / `user.email` bằng tài khoản GitHub của **chính mình** |

Kiểm tra extension PHP: `php -m`.

## 2. Cài đặt lần đầu

```bash
# 1. Clone và tạo nhánh riêng của mình (phu / sithiphak / khanxay)
git clone https://github.com/vietphubdp/techshop.git
cd techshop
git checkout -b khanxay
git push -u origin khanxay

# 2. Cài thư viện
composer install
npm install

# 3. Tạo file môi trường
cp .env.example .env        # Windows CMD: copy .env.example .env
```

Mở `.env`, sửa `DB_USERNAME` / `DB_PASSWORD` cho đúng MySQL trên máy mình.

Tạo CSDL `techshop` trong MySQL (phpMyAdmin, MySQL Workbench hoặc dòng lệnh):

```sql
CREATE DATABASE techshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
# 4. Tạo khóa ứng dụng, bảng và dữ liệu mẫu
php artisan key:generate
php artisan migrate:fresh --seed

# 5. Liên kết thư mục ảnh upload
php artisan storage:link

# 6. Chạy dự án (PHP server + Vite cùng lúc)
composer run dev
```

Mở http://localhost:8000.

Tài khoản admin mẫu (có sau lượt S1-L1): `admin@techshop.test` / `password`.

## 3. Mỗi lần bắt đầu làm việc

```bash
git checkout khanxay                    # nhánh của mình
git pull origin main                    # lấy code mới nhất đã được duyệt
composer install && npm install         # khi composer.lock / package-lock.json thay đổi
php artisan migrate                     # hoặc migrate:fresh --seed nếu lượt trước đổi migration/seeder
```

Làm xong: `git push origin khanxay` rồi mở Pull Request `khanxay → main` trên GitHub để Phú duyệt.

Quy trình commit, PR: xem `CLAUDE.md` mục 10 và `PLAN.md` mục 0.

## 4. Kiểm thử & code style

```bash
php artisan test        # chạy trên SQLite in-memory, không đụng DB MySQL
vendor/bin/pint         # định dạng code PSR-12 trước khi commit
```

## 5. Lỗi thường gặp

| Lỗi | Cách sửa |
|---|---|
| `could not find driver` | Bật `extension=pdo_mysql` trong `php.ini` |
| `Unknown database 'techshop'` | Chưa tạo DB ở bước 3 |
| `Vite manifest not found` | Chạy `npm run dev` (hoặc `composer run dev`) hoặc `npm run build` |
| Ảnh upload không hiện | Chạy `php artisan storage:link` |
