<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Apple\Provider;

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
        // Configure Apple Sign-In provider
        Socialite::extend('apple', function ($app) {
            $config = $app['config']['services.apple'];
            return Socialite::buildProvider(Provider::class, $config);
        });

        // Autorisation centralisée (anti-IDOR) — Policies d'ownership
        Gate::policy(\App\Models\Product::class, \App\Policies\ProductPolicy::class);
        Gate::policy(\App\Models\Lottery::class, \App\Policies\LotteryPolicy::class);
    }
}
