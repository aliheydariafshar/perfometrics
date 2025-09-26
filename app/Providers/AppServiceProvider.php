<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Services\BusinessRules\BusinessRule;
use App\Services\BusinessRules\NewEmployeeMinimumRule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BusinessRule::class, function ($app) {
            $cfg = $app['config']['performance']['business_rules'];
            return new NewEmployeeMinimumRule(
                (int) ($cfg['new_employee_min_months'] ?? 3),
                (float) ($cfg['new_employee_min_score'] ?? 50.0)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-default', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('api-heavy', function (Request $request) {
            return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
