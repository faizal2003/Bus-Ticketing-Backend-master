<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        view()->composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $companyName = \App\Models\Setting::get('company_name', 'Bus Ticketing');
                $logo = \App\Models\Setting::get('company_logo');
                $companyLogo = $logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo)
                    ? \Illuminate\Support\Facades\Storage::url($logo)
                    : null;
            } else {
                $companyName = 'Bus Ticketing';
                $companyLogo = null;
            }

            $view->with(compact('companyName', 'companyLogo'));
        });
    }
}
