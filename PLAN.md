# PLAN.md – Kế hoạch code "tiếp sức" Sprint 1 & Sprint 2

> Đọc cùng `CLAUDE.md`. File này cho biết **hôm nay ai làm, làm gì, làm xong bàn giao thế nào**.
> Nguồn: sheet Sprint 1, Sprint 2 trong `Buoi03_KeHoachAgile_TheoSprint_v2.xlsx` (mã task T-xx giữ nguyên để đối chiếu).

## 0. Cách làm việc tiếp sức

Mỗi sprint được chia thành các **lượt** chạy **lần lượt**. Mỗi lượt có **1 người** làm:

```
pull develop  →  nhờ AI code các task của lượt  →  test pass  →  push + PR  →  Phú merge  →  người kế tiếp pull
```

- Lượt sau **chỉ bắt đầu khi PR của lượt trước đã merge vào `develop`** (trừ khi cột "Phụ thuộc" ghi "không").
- N1, N2… = ngày thứ 1, 2… tính từ sau buổi học trước. Đây là ngày gợi ý, nhóm có thể dời nhưng **giữ thứ tự**.
- Phú review & merge PR (PR của chính Phú thì Sithiphak xem qua rồi Phú merge). Nếu Phú bận quá 12 giờ, người làm được tự merge khi `php artisan test` pass.
- Lượt làm không xong: vẫn push nhánh, ghi "còn dở" trong Nhật ký bàn giao, báo nhóm Zalo. Người sau làm tiếp nếu lượt của họ không phụ thuộc.
- Xong mỗi lượt: người làm cập nhật trạng thái task trong file Excel Kế hoạch Agile và ghi nhật ký thực hiện đề tài.

### Câu lệnh mẫu để mở đầu mỗi lượt với AI

```
Đọc CLAUDE.md và PLAN.md. Tôi là <Tên>, hôm nay làm lượt <S1-L3>.
Hãy pull develop, kiểm tra Nhật ký bàn giao, rồi làm lần lượt từng task của lượt này,
commit sau mỗi task. Xong thì chạy pint + test, cập nhật PLAN.md, push và hướng dẫn tôi mở PR.
Giải thích ngắn cho tôi từng phần để tôi trình bày được với thầy.
```

### Lệnh Git cơ bản (Windows, Git Bash / Terminal VS Code)

```bash
git checkout develop && git pull origin develop       # lấy code mới nhất
git checkout -b feature/s1-l3-auth                    # tạo nhánh cho lượt
git add . && git commit -m "feat(auth): ... [T-01]"   # commit
git push -u origin feature/s1-l3-auth                 # đẩy lên, rồi mở PR trên GitHub vào develop
```

Bị conflict khi mở PR → `git pull origin develop` ngay trên nhánh của mình, nhờ AI gộp conflict giúp, chạy lại test rồi push.

---

## SPRINT 1 – Xem hàng & quản trị sản phẩm (demo buổi 6)

**Mục tiêu:** có ít nhất 1 luồng chạy đầu–cuối: admin thêm danh mục, sản phẩm → khách đăng ký, đăng nhập, xem, lọc, xem chi tiết.
**Hạng mục:** FR-01, 02, 03, 04, 05, 09, 10 (27 point) + phần chuẩn bị còn lại của Sprint thiết kế (C-xx).

| Lượt | Ngày | Người | Nội dung | Phụ thuộc |
|---|---|---|---|---|
| S1-L0 | N1 | Phú | Chuẩn bị repo & môi trường | — |
| S1-L1 | N1–N2 | Sithiphak | CSDL: migration, model, seeder | L0 |
| S1-L2 | N2 | Khanxay | Layout khách | L0 (không cần L1) |
| S1-L3 | N3 | Phú | Đăng ký, đăng nhập, phân quyền | L1, L2 |
| S1-L4 | N3–N4 | Khanxay | Trang chủ, danh sách, chi tiết SP | L1, L2 |
| S1-L5 | N4–N5 | Sithiphak | Layout admin, quản lý danh mục & sản phẩm | L3 |
| S1-L6 | N5 | Khanxay | Tìm kiếm, lọc, sắp xếp | L4 |
| S1-L7 | N6 | Phú | Tích hợp, chạy kịch bản demo, merge `main` | tất cả |

> L4 và L5 sửa file khác nhau nên có thể làm song song nếu bị trễ.

### S1-L0 · Phú · Chuẩn bị repo & môi trường
- [ ] C-01 Đổi nhánh `master` → `main`, tạo `develop`; trên GitHub: đặt `develop` làm nhánh mặc định, bật bảo vệ `main` (bắt buộc PR); mời Sithiphak, Khanxay làm collaborator
- [x] C-02 `.env.example`: `APP_NAME=TechShop`, `APP_LOCALE=vi`, `APP_FAKER_LOCALE=vi_VN`, `DB_CONNECTION=mysql`, `DB_DATABASE=techshop`, `DB_USERNAME=root`, `DB_PASSWORD=` _(đã sửa sẵn – mỗi người tự sửa `.env` của mình theo, tạo DB `techshop` trong MySQL)_
- [x] C-03 Gỡ Tailwind, dùng Bootstrap 5.3 – **đã sửa sẵn** `package.json`, `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`; còn: xóa `node_modules`, chạy `npm install` rồi `npm run build` để xác nhận build được (trang `welcome` mặc định sẽ vỡ giao diện – bình thường, S1-L4 thay bằng trang chủ)
- [x] C-04 Tạo `lang/vi/validation.php` (thông báo tiếng Việt, có `attributes` cho các trường ở CLAUDE.md mục 7)
- [x] C-05 Blade directive `@money($v)` trong `AppServiceProvider`; `php artisan storage:link`
- [x] C-06 README: yêu cầu máy (PHP ≥ 8.3 bật `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `curl`, `zip`, `intl`; Composer 2; Node ≥ 20.19; MySQL 8) và các bước cài cho thành viên (clone, `composer install`, `npm install`, copy `.env`, tạo DB `techshop`, `key:generate`, `migrate:fresh --seed`, `storage:link`, `composer run dev`)
- [x] C-07 Commit `CLAUDE.md`, `PLAN.md`, `AGENTS.md`

**Cách kiểm tra:** cả 3 máy clone được, `php artisan serve` mở được trang chủ mặc định, Bootstrap đã nạp (F12 → không còn class Tailwind).

### S1-L1 · Sithiphak · CSDL
- [ ] C-08 Migration 7 bảng đúng CLAUDE.md mục 4 (sửa migration `users` mặc định để thêm phone, role, address, is_locked; đủ FK, index, onDelete)
- [ ] C-09 Model + quan hệ (`hasMany`/`belongsTo`) + `$fillable` + `casts`; `App\Enums\OrderStatus` (label tiếng Việt, `canTransitionTo()` theo BR-05); `Product::final_price` (BR-01), scope `active()`
- [ ] C-10 Factory: User (state `admin()`, `locked()`), Category, Product (state `outOfStock()`, `onSale()`), Order, OrderItem
- [ ] C-11 Seeder: 1 admin (`admin@techshop.test` / `password`), 2 khách, 6 danh mục, ~60 SP (có SP khuyến mãi, SP hết hàng, SP ẩn); `ProductFactory` sinh được 500 SP để đo NFR-01 (seeder riêng `PerformanceSeeder`, không chạy mặc định)

**Cách kiểm tra:** `php artisan migrate:fresh --seed` chạy sạch trên MySQL; mở DB thấy đủ 7 bảng, khóa ngoại đúng.

### S1-L2 · Khanxay · Layout khách
- [ ] T-07 `layouts/app.blade.php` + partials `header` (logo, ô tìm kiếm, nút đăng nhập/tài khoản, giỏ hàng), `nav` danh mục, `footer`, `flash`; partial `product-card` dùng chung (ảnh, giá, giá KM, nút thêm giỏ/hết hàng)

**Cách kiểm tra:** trang tạm dùng layout hiển thị đúng wireframe Hình 2 ở 1366px và 360px.

### S1-L3 · Phú · Đăng ký, đăng nhập, phân quyền (FR-01, FR-02)
- [ ] Khung `routes/web.php` đủ 3 nhóm route (khách / tài khoản / admin) theo CLAUDE.md mục 5 – route chưa làm thì để comment
- [ ] T-01 Route + view form đăng ký (wireframe Hình 5)
- [ ] T-02 `RegisterRequest` + `AuthController@register`
- [ ] T-03 Feature test đăng ký (thành công, email trùng)
- [ ] T-04 Đăng nhập/đăng xuất, chuyển hướng theo vai trò (admin → `/admin`, khách → trang trước)
- [ ] T-05 Middleware `role:admin` và `not_locked` (đăng ký alias trong `bootstrap/app.php`)
- [ ] T-06 Feature test đăng nhập, tài khoản khóa, chặn `/admin` với khách

**Cách kiểm tra:** đăng ký tài khoản mới; đăng nhập admin seed → vào `/admin`; khách gõ `/admin` → 403.

### S1-L4 · Khanxay · Trang chủ, danh sách, chi tiết (FR-03, FR-05)
- [ ] T-08 Trang chủ: SP mới, SP khuyến mãi (wireframe Hình 2)
- [ ] T-09 Trang `/san-pham` phân trang 12 SP (Hình 3)
- [ ] T-14 Trang chi tiết sản phẩm (Hình 4 – phần đánh giá chỉ hiển thị, form đánh giá để Sprint 3)
- [ ] T-15 Khóa nút khi hết hàng, 404 khi SP ẩn + test

**Cách kiểm tra:** dữ liệu seed hiển thị đúng giá KM, SP hết hàng bị khóa nút, gõ slug SP ẩn → 404.

### S1-L5 · Sithiphak · Quản trị danh mục & sản phẩm (FR-09, FR-10)
- [ ] T-16 `layouts/admin.blade.php` + sidebar (đủ mục như wireframe Hình 12–17)
- [ ] T-17 CRUD danh mục + tự sinh slug (Hình 13)
- [ ] T-18 Chặn xóa danh mục còn SP + test
- [ ] T-19 Form thêm/sửa sản phẩm (Hình 14)
- [ ] T-20 Upload ảnh (`ImageService`, disk public, xóa ảnh cũ khi đổi)
- [ ] T-21 Ẩn/hiện, tìm kiếm/lọc trong admin; nút Xóa chỉ với SP chưa có trong đơn
- [ ] T-22 `ProductRequest` + `AdminProductTest`

**Cách kiểm tra:** admin thêm danh mục → thêm SP có ảnh → SP hiện ở trang khách; ẩn SP → biến mất khỏi trang khách.

### S1-L6 · Khanxay · Tìm kiếm & lọc (FR-04)
- [ ] T-10 Tìm kiếm theo tên
- [ ] T-11 Lọc danh mục + khoảng giá
- [ ] T-12 Sắp xếp (mới nhất, giá tăng, giá giảm) + giữ bộ lọc khi phân trang
- [ ] T-13 Kiểm tra giao diện ở 360px (trang chủ, danh sách, chi tiết, đăng nhập, đăng ký)

**Cách kiểm tra:** `/san-pham?q=tai&category=tai-nghe&min_price=200000&sort=price_asc` ra đúng, sang trang 2 vẫn giữ bộ lọc.

### S1-L7 · Phú · Tích hợp Sprint 1
- [ ] I-01 Pull `develop`, `migrate:fresh --seed`, `php artisan test` pass toàn bộ
- [ ] I-02 Chạy kịch bản demo: admin thêm danh mục, SP có ảnh → khách đăng ký, đăng nhập, lọc, xem chi tiết; khách bị chặn `/admin`
- [ ] I-03 Sửa lỗi tích hợp nhỏ (lỗi lớn → ghi Nhật ký, giao lại người phụ trách)
- [ ] I-04 Merge `develop` → `main`, gắn tag `v0.1-sprint1`; ghi "Kết quả demo" vào sheet Sprint 1

---

## SPRINT 2 – Luồng mua hàng & tích hợp (mốc giữa kỳ 2, buổi 7)

**Mục tiêu:** luồng mua hàng đầu–cuối chạy trên bản tích hợp; có quyết định go/no-go.
**Hạng mục:** FR-06, 07, 08, 11 (26 point) + tích hợp.

| Lượt | Ngày | Người | Nội dung | Phụ thuộc |
|---|---|---|---|---|
| S2-L1 | N1 | Khanxay | Giỏ hàng | Sprint 1 |
| S2-L2 | N2 | Phú | Đặt hàng (checkout) + trang thành công | L1 |
| S2-L3 | N3 | Sithiphak | Seeder đơn mẫu + danh sách đơn admin | không (chỉ cần model Order) |
| S2-L4 | N3–N4 | Phú | Đơn hàng của tôi + hủy đơn | L2 |
| S2-L5 | N4–N5 | Sithiphak | Chi tiết đơn admin + chuyển trạng thái | L3, L4 (`OrderService::cancel`) |
| S2-L6 | N5 | Khanxay | Ráp giao diện, kiểm tra 360px | L5 |
| S2-L7 | N6 | Phú | Tích hợp, go/no-go, merge `main` | tất cả |

### S2-L1 · Khanxay · Giỏ hàng (FR-06)
- [ ] T-23 `CartService` lưu session (add, update, remove, items, subtotal – dùng `final_price`)
- [ ] T-24 `CartController` + view giỏ hàng (wireframe Hình 6 – không có ô mã giảm giá)
- [ ] T-25 Nút thêm giỏ ở thẻ SP & trang chi tiết + số lượng trên icon giỏ ở header
- [ ] T-26 Kiểm tra lại tồn kho khi mở giỏ, tự giảm số lượng + flash thông báo (BR-03)
- [ ] T-27 `CartTest`

### S2-L2 · Phú · Đặt hàng (FR-07)
- [ ] T-28 `OrderService::placeOrder` (transaction + `lockForUpdate`, lưu giá lúc mua, trừ kho, gán mã TS-xxxx – BR-04, BR-10)
- [ ] T-29 `CheckoutRequest`, form giao hàng (Hình 7, điền sẵn từ hồ sơ), trang đặt hàng thành công `/dat-hang-thanh-cong/{code}` (Hình 8)
- [ ] T-30 Xóa giỏ sau khi đặt; khách chưa đăng nhập bấm thanh toán → đăng nhập rồi quay lại
- [ ] T-31 `CheckoutTest` (thành công, hết hàng giữa chừng → rollback, giỏ rỗng)

### S2-L3 · Sithiphak · Danh sách đơn admin (FR-11 phần 1)
- [ ] T-37a `OrderSeeder`: đơn mẫu đủ 5 trạng thái cho 2 khách (dùng giá SP lúc seed)
- [ ] T-35 Danh sách đơn + lọc trạng thái / mã / khoảng ngày, đếm số đơn theo trạng thái (Hình 15)

### S2-L4 · Phú · Đơn hàng của tôi (FR-08)
- [ ] T-32 Lịch sử + chi tiết đơn (Hình 9, 10), chặn xem đơn người khác (403)
- [ ] T-33 Hủy đơn khi pending, hoàn tồn kho (`OrderService::cancel` – BR-06)
- [ ] T-34 Test hủy đơn (hủy được khi pending, không hủy được khi confirmed, kho được cộng lại)

### S2-L5 · Sithiphak · Chuyển trạng thái đơn (FR-11 phần 2)
- [ ] T-36 Chi tiết đơn admin + đổi trạng thái theo `OrderStatus::canTransitionTo()`; chọn "Đã hủy" → gọi `OrderService::cancel` (Hình 16)
- [ ] T-37b `AdminOrderTest` (chuyển hợp lệ, chuyển sai luồng bị chặn, hủy hoàn kho)

### S2-L6 · Khanxay · Ráp giao diện
- [ ] I-05 Nối link giữa các màn: header → "Đơn hàng của tôi", trang thành công → chi tiết đơn, menu tài khoản
- [ ] I-06 Kiểm tra 360px các trang mới (giỏ, thanh toán, thành công, đơn hàng, admin đơn hàng)
- [ ] I-07 Chạy thử luồng mua hàng trên giao diện, sửa lỗi hiển thị

### S2-L7 · Phú · Tích hợp & go/no-go
- [ ] I-08 `migrate:fresh --seed`, `php artisan test` pass toàn bộ
- [ ] I-09 Chạy kịch bản demo GK2: thêm giỏ → checkout COD → admin xác nhận → đang giao → hoàn thành → khách thấy trạng thái; thử hủy đơn pending → kho được cộng lại
- [ ] I-10 Điền bảng go/no-go (mục cuối file này) cùng cả nhóm; cập nhật backlog Sprint 3 nếu phải cắt phạm vi
- [ ] I-11 Merge `develop` → `main`, tag `v0.2-sprint2`

---

## Sprint 3 trở đi
Lập chi tiết sau buổi 7 theo quyết định go/no-go (sheet "Sprint 3 (B8)": FR-12, 13, 14, 15 + kiểm thử; FR-16, 18 là dự phòng).

## Bảng go/no-go (điền ở S2-L7)

| Tiêu chí | Đạt? | Ghi chú |
|---|---|---|
| Toàn bộ FR Must của Sprint 1 + 2 chạy được | | |
| Luồng mua hàng đầu–cuối chạy trên `main` | | |
| `php artisan test` pass 100% | | |
| Cả 3 máy chạy được bản `main` | | |
| **Quyết định** (Go / Go có điều chỉnh / No-go) | | |

## Thay đổi so với thiết kế
_(AI ghi vào đây khi buộc phải làm khác tài liệu buổi 5: đổi cột, thêm route… để nhóm cập nhật tài liệu)_

| Ngày | Lượt | Thay đổi | Lý do |
|---|---|---|---|
| | | | |

## Nhật ký bàn giao
_(Mỗi lượt ghi thêm 1 mục ở cuối, KHÔNG xóa mục cũ)_

<!-- Mẫu:
### S1-L1 · Sithiphak · dd/mm
- Đã xong: C-08, C-09, C-10, C-11
- File chính: database/migrations/..., app/Models/..., app/Enums/OrderStatus.php
- Người sau cần chạy: php artisan migrate:fresh --seed
- Lưu ý: ...
- Còn dở: (không)
-->

### S1-L0 · Phú · 25/10
- Đã xong: C-02, C-03 (`npm run build` OK), C-04, C-05, C-06, C-07 (điều chỉnh – xem lưu ý); C-01 phần Git local + push nhánh `main`, `develop`
- File chính: `lang/vi/validation.php`, `app/Providers/AppServiceProvider.php` (`@money`), `tests/Feature/MoneyDirectiveTest.php`, `README.md`, `phpunit.xml` (thêm `APP_LOCALE=vi` để test luôn chạy tiếng Việt), `.gitignore`
- Người sau cần chạy: `git checkout develop && git pull origin develop`, `npm install`, `php artisan storage:link`; sửa `.env` theo `.env.example` (`APP_LOCALE=vi`, MySQL `techshop`)
- Lưu ý:
  - **CLAUDE.md không còn nằm trong Git** (theo yêu cầu của Phú – đã `git rm --cached` + thêm vào `.gitignore`). Bản cũ trên repo là hướng dẫn mặc định của Laravel Boost, sai luật nhóm. Mỗi người nhận CLAUDE.md từ Phú qua Zalo, đặt ở thư mục gốc repo. Khi pull lần đầu, Git sẽ xóa CLAUDE.md cũ trong máy → chép bản mới vào sau khi pull.
  - Nhánh `main` vẫn còn CLAUDE.md bản Boost (commit khởi tạo) đến khi merge `develop` → `main` ở S1-L7.
  - `lang/vi/validation.php` có sẵn `custom` cho `phone`, `receiver_phone` (regex) và `sale_price` (lt) – Form Request không cần tự viết `messages()` cho các lỗi này.
  - Hiển thị tiền trong Blade: `@money($product->final_price)` → `1.500.000đ`.
- Còn dở: C-01 phần trên GitHub (Phú làm tay): đặt `develop` làm nhánh mặc định, xóa nhánh `master` trên remote, bảo vệ `main`, mời collaborator. Làm xong thì đánh ✅ C-01.
