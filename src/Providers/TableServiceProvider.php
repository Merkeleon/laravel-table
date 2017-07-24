<?php

namespace Merkeleon\Table\Providers;

use Illuminate\Support\ServiceProvider;
use Merkeleon\Table\Table;

class TableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(dirname(__DIR__) . '/resources/views', 'table');
        $this->loadTranslationsFrom(dirname(__DIR__) . '/resources/lang', 'table');
        $this->publishes([
            dirname(__DIR__) . '/resources/views' => resource_path('views/vendor/table'),
            dirname(__DIR__) . '/resources/lang' => resource_path('lang/vendor/table'),
            dirname(__DIR__) . '/config/table.php' => config_path('table.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/table.php', 'table'
        );

        $this->app->bind('table', function ($app) {
            return new Table();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['table', 'Merkeleon\Table'];
    }
}
