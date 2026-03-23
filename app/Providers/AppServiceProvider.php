<?php

namespace App\Providers;

use App\Auth\EmailHashUserProvider;
use Illuminate\Support\Facades\Auth;
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
        Auth::provider('email_hash_eloquent', function ($app, array $config) {
            return new EmailHashUserProvider(
                $app['hash'],
                $config['model']
            );
        });
    }
}
