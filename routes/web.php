<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
| Nhóm khách – không cần đăng nhập
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang', [CartController::class, 'add'])->name('cart.add');
Route::patch('/gio-hang/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{productId}', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware('guest')->group(function () {
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register']);
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login']);
});

/*
| Nhóm tài khoản – phải đăng nhập và không bị khóa
*/
Route::middleware(['auth', 'not_locked'])->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');

    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    // Mã giảm giá (Could – FR-16), mở khi làm Sprint 3:
    // Route::post('/thanh-toan/ma-giam-gia', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
    // Route::delete('/thanh-toan/ma-giam-gia', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::get('/dat-hang-thanh-cong/{code}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/tai-khoan/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/tai-khoan/don-hang/{code}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/tai-khoan/don-hang/{code}/huy', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::post('/san-pham/{slug}/danh-gia', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/tai-khoan', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/tai-khoan/mat-khau', [ProfileController::class, 'password'])->name('profile.password');
});

/*
| Nhóm quản trị – chỉ admin
*/
Route::middleware(['auth', 'not_locked', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', Admin\CategoryController::class)->except('show');
    Route::resource('products', Admin\ProductController::class)->except('show');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/lock', [Admin\UserController::class, 'toggleLock'])->name('users.lock');

    // Mã giảm giá (Could – FR-16), mở khi làm Sprint 3:
    // Route::resource('coupons', Admin\CouponController::class)->except('show');
});
