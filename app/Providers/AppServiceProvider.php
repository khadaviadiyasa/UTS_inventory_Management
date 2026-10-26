<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register application services here when the project grows.
    }

    public function boot(): void
    {
        // No global data or secrets are injected into views.
    }
}