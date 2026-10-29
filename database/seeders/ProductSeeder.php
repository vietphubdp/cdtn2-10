<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Khoảng 60 SP demo, 10 SP mỗi danh mục.
     * Mỗi dòng: [tên, giá gốc, giá KM (null = không KM), tồn kho, mô tả, đang hiện?]
     * Có đủ trường hợp: SP khuyến mãi, SP hết hàng (tồn kho 0), SP ẩn (false).
     */
    public function run(): void
    {
        $products = [
            'tai-nghe' => [
                ['Tai nghe Sony WH-1000XM5', 8490000, 7490000, 15, 'Tai nghe chụp tai chống ồn chủ động, pin 30 giờ, sạc nhanh USB-C.'],
                ['Tai nghe Apple AirPods Pro 2 USB-C', 6190000, 5590000, 20, 'Chống ồn chủ động, xuyên âm, hộp sạc USB-C có loa tìm thiết bị.'],
                ['Tai nghe Apple AirPods 4', 3490000, null, 25, 'Thiết kế mở, chip H2, âm thanh không gian cá nhân hóa.'],
                ['Tai nghe Samsung Galaxy Buds3 Pro', 4990000, null, 0, 'Chống ồn thông minh, âm thanh Hi-Fi 24-bit, chống nước IP57.'],
                ['Tai nghe JBL Tune 520BT', 1290000, 990000, 30, 'Tai nghe chụp tai gấp gọn, âm bass JBL Pure Bass, pin 57 giờ.'],
                ['Tai nghe Soundcore Life Q30', 1890000, null, 18, 'Chống ồn đa chế độ, hỗ trợ Hi-Res, pin 40 giờ.'],
                ['Tai nghe Sennheiser Momentum 4 Wireless', 8990000, null, 6, 'Âm thanh chuẩn audiophile, pin 60 giờ, chống ồn thích ứng.'],
                ['Tai nghe Xiaomi Redmi Buds 6 Active', 390000, null, 50, 'Tai nghe true wireless giá tốt, pin 30 giờ kèm hộp sạc.'],
                ['Tai nghe Sony WF-C700N', 2490000, 1990000, 12, 'Tai nghe nhỏ gọn, chống ồn, chống nước IPX4.'],
                ['Tai nghe Edifier W820NB Plus', 1190000, null, 10, 'Chống ồn ANC, hỗ trợ LDAC, pin 49 giờ.', false],
            ],
            'sac-du-phong' => [
                ['Sạc dự phòng Anker 323 10000mAh', 590000, null, 40, 'Nhỏ gọn, 2 cổng sạc, phù hợp mang đi học, đi làm.'],
                ['Sạc dự phòng Xiaomi 20000mAh 22.5W', 690000, 590000, 35, 'Dung lượng lớn, sạc nhanh 22.5W, sạc được 3 thiết bị.'],
                ['Sạc dự phòng Baseus Magnetic Mini 10000mAh 20W', 790000, null, 22, 'Hít nam châm mặt lưng iPhone, sạc không dây 15W.'],
                ['Sạc dự phòng Anker 737 24000mAh 140W', 2990000, 2590000, 8, 'Sạc được laptop, màn hình hiển thị công suất.'],
                ['Sạc dự phòng Ugreen 25000mAh 145W', 2190000, null, 0, 'Công suất 145W, 3 cổng, sạc 2 laptop cùng lúc.'],
                ['Sạc dự phòng Samsung 10000mAh 25W', 890000, null, 15, 'Sạc siêu nhanh 25W cho điện thoại Galaxy, vỏ nhôm.'],
                ['Sạc dự phòng Belkin BoostCharge 10000mAh', 790000, null, 10, 'Thiết kế mỏng, cổng USB-C PD 15W.'],
                ['Sạc dự phòng Energizer UE10054 10000mAh', 450000, null, 20, 'Bền bỉ, có đèn báo dung lượng pin.'],
                ['Sạc dự phòng Baseus Blade 20000mAh 100W', 1690000, 1490000, 9, 'Mỏng như cuốn sổ, sạc nhanh laptop 100W.'],
                ['Sạc dự phòng Innostyle PowerGo 10000mAh', 490000, null, 25, 'Nhỏ gọn, sạc nhanh 20W, cổng USB-C và USB-A.'],
            ],
            'cu-sac-va-cap' => [
                ['Củ sạc Apple USB-C 20W', 550000, 490000, 60, 'Sạc nhanh iPhone, chính hãng Apple.'],
                ['Củ sạc Anker 511 Nano 3 30W', 590000, null, 40, 'Công nghệ GaN nhỏ gọn, sạc nhanh iPhone và iPad.'],
                ['Củ sạc Ugreen Nexode 65W GaN 3 cổng', 1090000, 890000, 25, '2 cổng USB-C và 1 cổng USB-A, sạc được laptop.'],
                ['Củ sạc Samsung 25W', 390000, null, 45, 'Sạc siêu nhanh cho điện thoại Galaxy.'],
                ['Củ sạc Baseus GaN5 Pro 100W', 1290000, null, 0, 'Công suất 100W, 4 cổng, kèm cáp USB-C 1m.'],
                ['Cáp Apple USB-C to Lightning 1m', 490000, null, 70, 'Cáp chính hãng, hỗ trợ sạc nhanh iPhone.'],
                ['Cáp Anker PowerLine III USB-C to USB-C 1.8m', 390000, null, 50, 'Hỗ trợ sạc 60W, độ bền cao.'],
                ['Cáp Ugreen USB-C 100W 2m bện dù', 290000, 250000, 55, 'Vỏ bện dù chống đứt, sạc laptop 100W.'],
                ['Cáp Baseus 3 trong 1 Lightning, USB-C, Micro USB', 190000, null, 80, 'Một cáp dùng cho nhiều loại thiết bị.'],
                ['Cáp Xiaomi USB-A to USB-C 1m', 120000, null, 90, 'Cáp sạc và truyền dữ liệu cơ bản.', false],
            ],
            'chuot' => [
                ['Chuột Logitech MX Master 3S', 2490000, 2190000, 14, 'Cảm biến 8000 DPI, click yên tĩnh, kết nối 3 thiết bị.'],
                ['Chuột Logitech G102 Lightsync', 450000, null, 60, 'Chuột gaming có dây, đèn RGB, 8000 DPI.'],
                ['Chuột Logitech M331 Silent Plus', 350000, null, 45, 'Chuột không dây click im lặng, pin 24 tháng.'],
                ['Chuột Razer DeathAdder Essential', 590000, null, 30, 'Thiết kế công thái học, cảm biến 6400 DPI.'],
                ['Chuột Logitech G304 Lightspeed', 790000, 690000, 25, 'Chuột gaming không dây độ trễ thấp, cảm biến HERO.'],
                ['Chuột Apple Magic Mouse USB-C', 2290000, null, 7, 'Bề mặt cảm ứng đa điểm, sạc qua cổng USB-C.'],
                ['Chuột Microsoft Bluetooth Mouse', 450000, null, 0, 'Kết nối Bluetooth, thiết kế đối xứng.'],
                ['Chuột Xiaomi Wireless Mouse Lite', 190000, null, 50, 'Chuột không dây giá rẻ, kết nối USB 2.4GHz.'],
                ['Chuột Razer Basilisk V3', 1490000, null, 12, '11 nút lập trình, con lăn HyperScroll, đèn RGB.'],
                ['Chuột Logitech Pebble Mouse 2 M350s', 490000, null, 35, 'Mỏng nhẹ, click im lặng, kết nối Bluetooth.'],
            ],
            'ban-phim' => [
                ['Bàn phím Logitech MX Keys S', 2790000, 2490000, 10, 'Phím chiclet êm, đèn nền thông minh, kết nối 3 thiết bị.'],
                ['Bàn phím Logitech K380', 790000, null, 30, 'Bàn phím Bluetooth nhỏ gọn cho máy tính bảng và điện thoại.'],
                ['Bàn phím cơ Keychron K2 Pro', 2490000, null, 8, 'Layout 75%, hot-swap, hỗ trợ Mac và Windows.'],
                ['Bàn phím cơ Akko 3068B Plus', 1690000, 1490000, 15, 'Layout 65%, 3 chế độ kết nối, keycap PBT.'],
                ['Bàn phím cơ DareU EK87', 590000, null, 40, 'Layout TKL, switch cơ, đèn LED.'],
                ['Bàn phím cơ Razer BlackWidow V4 X', 3290000, null, 0, 'Switch Razer Green, 6 phím macro, đèn RGB.'],
                ['Bàn phím Apple Magic Keyboard USB-C', 2590000, null, 6, 'Thiết kế mỏng, pin sạc, kết nối Bluetooth.'],
                ['Bàn phím Logitech G213 Prodigy', 990000, null, 20, 'Bàn phím gaming chống nước, phím đa phương tiện.'],
                ['Bàn phím Rapoo E9050G', 590000, null, 18, 'Bàn phím không dây mỏng, kết nối đa thiết bị.'],
                ['Bàn phím cơ E-Dra EK387 Pro', 790000, null, 12, 'Layout TKL, switch Huano, đèn RGB.', false],
            ],
            'loa-bluetooth' => [
                ['Loa bluetooth JBL Flip 6', 2990000, 2490000, 16, 'Chống nước IP67, âm thanh mạnh mẽ, pin 12 giờ.'],
                ['Loa bluetooth JBL Go 4', 1090000, null, 40, 'Nhỏ gọn, có dây đeo, chống nước IP67.'],
                ['Loa bluetooth Sony SRS-XB100', 1290000, null, 25, 'Loa mini chống nước, pin 16 giờ.'],
                ['Loa bluetooth Marshall Emberton II', 4490000, null, 5, 'Âm thanh 360 độ, pin hơn 30 giờ.'],
                ['Loa bluetooth Soundcore Motion 300', 1990000, 1690000, 12, 'Hỗ trợ Hi-Res, công suất 30W.'],
                ['Loa bluetooth Xiaomi Mi Portable Speaker 16W', 790000, null, 30, 'Chống nước IPX7, ghép đôi 2 loa.'],
                ['Loa bluetooth JBL Charge 5', 3990000, null, 0, 'Pin 20 giờ, kiêm sạc dự phòng cho điện thoại.'],
                ['Loa bluetooth Bose SoundLink Flex', 3690000, null, 8, 'Chống nước, chống va đập, âm thanh rõ.'],
                ['Loa bluetooth Harman Kardon Onyx Studio 8', 5990000, 5290000, 4, 'Thiết kế sang trọng, âm thanh stereo.'],
                ['Loa bluetooth Tronsmart T7', 1190000, null, 20, 'Công suất 30W, đèn LED, chống nước IPX7.'],
            ],
        ];

        foreach ($products as $categorySlug => $rows) {
            $category = Category::where('slug', $categorySlug)->firstOrFail();

            foreach ($rows as $row) {
                [$name, $price, $salePrice, $stock, $description] = $row;

                Product::create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'stock' => $stock,
                    'image' => null,
                    'description' => $description,
                    'is_active' => $row[5] ?? true,
                ]);
            }
        }
    }
}
