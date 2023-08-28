<?php

namespace Celysium\Payment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function boot()
    {

        $this->mergeConfigFrom(__DIR__ . '/../config/payment.php', 'payment');
        /**
         * Configurations that needs to be done by user.
         */
        $this->publishes(
            [
                __DIR__ . '/../config/payment.php' => config_path('payment.php'),
            ],
            'config-gateway'
        );

        $this->loadViewsFrom([__DIR__ . '/../resources/views'], 'payment');
        $this->loadRoutesFrom(__DIR__.'/Routes/Payment.php');

    }

    public function register()
    {
        //
    }
}
