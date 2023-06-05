<?php

namespace Celysium\Payment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function boot()
    {

        $this->mergeConfigFrom( __DIR__ . '/../config/payment.php', 'payment');
        /**
         * Configurations that needs to be done by user.
         */
        $this->publishes(
            [
                dirname(__DIR__).'/config/payment.php' => config_path('payment.php'),
            ],
            'config-gateway'
        );

        $this->loadViewsFrom([__DIR__ .'/resources/views'], 'payemnt');

    }

    public function register()
    {
        //
    }
}
