<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
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
        Paginator::defaultView('pagination::custom');
        Paginator::defaultSimpleView('pagination::custom');

        $this->registerApiRateLimiters();
    }

    /**
     * API rate limiters: stricter on auth, moderate on financial workflow
     * actions, generous on read/AI endpoints.
     */
    private function registerApiRateLimiters(): void
    {
        RateLimiter::for('api-auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('api-financial', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('api-read', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
    }
}
