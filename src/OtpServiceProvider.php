<?php

namespace Itsmeabde\EasyOtp;

use Illuminate\Support\ServiceProvider;

class OtpServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $config = __DIR__.'/../config/otp.php', 'otp'
        );

        $this->app->alias('otp', Otp::class);
        $this->app->singleton('otp', function () {
            return new Otp;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/otp.php' => config_path('otp.php'),
        ]);

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/otp'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'otp');
    }
}