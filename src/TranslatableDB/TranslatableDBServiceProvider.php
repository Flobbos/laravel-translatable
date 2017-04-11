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
