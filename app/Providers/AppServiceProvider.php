<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
    }
}
