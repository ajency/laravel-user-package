<?php

namespace Ajency\Auth;

use Illuminate\Support\ServiceProvider;

class LaravelAjUserProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        
        // $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->publishes([
            /* Migrations file shifted */
            __DIR__.'/config/aj_auth_config.php' => config_path('aj_auth_config.php'),
            __DIR__.'/config/aj_migrations.php' => config_path('aj_auth_migrations.php'),

            /* Controller files shifted */
            /*__DIR__.'/Controllers/SocialAuthController.php' => public_path('app/Http/Controllers/Ajency/Auth/SocialAuthController.php'),

            __DIR__.'/Models/SocialAccountService.php' => public_path('app/SocialAccountService.php'),*/

        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
