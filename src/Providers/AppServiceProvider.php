<?php

namespace Innoboxrr\LaravelUploads\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register()
    {
        
        $this->mergeConfigFrom(__DIR__ . '/../../config/laravel-uploads.php', 'laravel-uploads');

    }

    public function boot()
    {
        
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // $this->loadViewsFrom(__DIR__.'/../../resources/views', 'innoboxrrlaraveluploads');

        if ($this->app->runningInConsole()) {
            
            // $this->publishes([__DIR__.'/../../resources/views' => resource_path('views/vendor/innoboxrrlaraveluploads'),], 'views');

            $this->publishes([__DIR__.'/../../config/laravel-uploads.php' => config_path('laravel-uploads.php')], 'config');

        }

    }
    
}