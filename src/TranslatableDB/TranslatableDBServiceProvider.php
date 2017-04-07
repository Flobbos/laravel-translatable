<?php

namespace Flobbos\TranslatableDB;

use Illuminate\Support\ServiceProvider;

class TranslatableDBServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/translatable.php' => config_path('translatable.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/translatabledb.php', 'translatable'
        );
    }
}
