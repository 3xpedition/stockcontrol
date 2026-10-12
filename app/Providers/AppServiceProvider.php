<?php

namespace App\Providers;

use App\Models\Product;
use App\Support\SecureRouteId;
use Illuminate\Support\Facades\Route;
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
        Route::bind('product', function (string $value): Product {
            return Product::whereKey(SecureRouteId::decrypt($value))->firstOrFail();
        });
    }
}
