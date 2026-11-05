<footer class="site-footer mt-auto">
    <div class="container">
        {{-- Khung đen nổi lên trên chân trang --}}
        <div class="footer-cta d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <h2 class="display-title mb-0">Phụ kiện chính hãng,<br>giao tận nơi</h2>
            <div class="d-flex flex-column gap-2 footer-cta-actions">
                <a href="{{ route('products.index') }}" class="btn btn-light">Xem tất cả sản phẩm</a>
                @guest
                    <a href="{{ route('register') }}" class="btn btn-outline-light">Tạo tài khoản</a>
                @else
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-light">Đơn hàng của tôi</a>
                @endguest
            </div>
        </div>

        <div class="row g-4 py-5">
            <div class="col-lg-4">
                <div class="site-logo mb-3">CDTN2-10</div>
                <p class="text-secondary small mb-0 footer-about">
                    Phụ kiện công nghệ chính hãng: tai nghe, sạc, cáp, ốp lưng, chuột, bàn phím, loa.
                </p>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="footer-heading">Mua sắm</div>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li><a href="{{ route('products.index') }}">Tất cả sản phẩm</a></li>
                    <li><a href="{{ route('cart.index') }}">Giỏ hàng</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="footer-heading">Liên hệ</div>
                <ul class="list-unstyled footer-links">
                    <li>Hotline: 1900 0000</li>
                    <li>Email: hotro@admin.test</li>
                </ul>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="footer-heading">Chính sách</div>
                <ul class="list-unstyled footer-links">
                    <li>Thanh toán khi nhận hàng (COD)</li>
                    <li>Đổi trả trong 7 ngày nếu lỗi do nhà sản xuất</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 py-4 small text-secondary">
            <span>© {{ date('Y') }} CDTN2-10</span>
            <span class="footer-chip">COD</span>
        </div>
    </div>
</footer>
