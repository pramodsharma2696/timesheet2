<?php

namespace App\Providers;

use Flugg\Responder\Contracts\Transformers\TransformerResolver;
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
        $this->app->make(TransformerResolver::class)->bind([
        'App\Models\User' => 'App\Transformers\UserTransformer',

        ]);
    }
}
