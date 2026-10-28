<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @money($v) → 1.500.000đ (tiền VND, không có số lẻ)
        Blade::directive('money', function (string $expression) {
            return "<?php echo number_format((int) ($expression), 0, ',', '.') . 'đ'; ?>";
        });

        // Dữ liệu dùng chung cho header và thanh danh mục của layout khách
        View::composer('partials.header', function ($view) {
            // Giỏ hàng lưu trong session 'cart' dạng [product_id => số lượng]
            $view->with('cartCount', array_sum(session('cart', [])));
        });

        View::composer('partials.nav', function ($view) {
            $view->with('navCategories', Category::orderBy('name')->get());
        });
    }
}
