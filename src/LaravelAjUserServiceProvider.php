<?php

namespace Ajency\User;

use Illuminate\Support\ServiceProvider;

class LaravelAjUserServiceProvider extends ServiceProvider {
    /**
     * Bootstrap the application services.
     *
     * @return void
     */

    protected $commands = [
        //Commands\CustomMigrations::class,
        "Ajency\User\Commands\CustomMigrationsCommand",
    ];

    public function boot() {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        
        // $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->publishes([
            /* Migrations file shifted */
            __DIR__.'/config/aj_user_config.php' => config_path('aj_user_config.php'),
            __DIR__.'/config/aj_user_migrations.php' => config_path('aj_user_migrations.php'),

            /* Controller files shifted */
            __DIR__.'/Controllers/SocialAuthController.php' => public_path('app/Http/Controllers/Ajency/User/SocialAuthController.php'),

            /*__DIR__.'/Models/SocialAccountService.php' => public_path('app/SocialAccountService.php'),*/

        ]);

        /* Command to Generate Model & Migration files in Application level */
        $this->commands($this->commands);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        //
        //dd("Package successfully mapped");
    }
}