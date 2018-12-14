<?php

namespace Flobbos\TranslatableDB;

use Illuminate\Support\ServiceProvider;

class TranslatableDBServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/translatabledb.php' => config_path('translatabledb.php'),
        ]);
        //Register the middleware
        if(config('translatabledb.middleware_default')){
            $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware(Middleware\LanguageIdentification::class);
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/translatabledb.php', 'translatabledb'
        );
    }
}
