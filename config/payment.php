<?php

use Celysium\Payment\Drivers\Ayria;
use Celysium\Payment\Drivers\Behpardakht;
use Celysium\Payment\Drivers\Local;
use Celysium\Payment\Drivers\Saman;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following gateway to use.
    | You can switch to a different driver at runtime.
    |
    */
    'default' => 'behpardakht',

    /*
    |--------------------------------------------------------------------------
    | List of Drivers
    |--------------------------------------------------------------------------
    |
    | These are the list of drivers to use for this package.
    | You can change the name. Then you'll have to change
    | it in the map array too.
    |
    */
    'drivers' => [
        'local' => [
            'gateway' => Local::class,
            'apiPaymentUrl' => '/local-payment/pay',
            'apiCallbackUrl' => '/local-payment/callback',
        ],
        'ayria' => [
            'gateway' => Ayria::class,
            'apiPurchaseUrl' => 'https://api.ayriaclub.ir/apg/v1/create',
            'apiVerificationUrl' => 'https://api.ayriaclub.ir/apg/v1/verify/',
            'referralCode' => env('AYRIA_REFERRAL_CODE'),
            'walletId' => env('AYRIA_WALLET_ID'),
            'apiKey' => env('AYRIA_API_KEY'),
            'callbackUrl' => env('BEHPARDAKHT_CALLBACK'),
            'description' => 'payment using apg',
        ],
        'behpardakht' => [
            'gateway' => Behpardakht::class,
            'apiPurchaseUrl' => 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl',
            'apiPaymentUrl' => 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat',
            'apiVerificationUrl' => 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl',
            'apiRefundUrl' => 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl',
            'terminalId' => env('BEHPARDAKHT_TERMINAL_ID'),
            'username' => env('BEHPARDAKHT_USERNAME'),
            'password' => env('BEHPARDAKHT_PASSWORD'),
            'callbackUrl' => env('BEHPARDAKHT_CALLBACK'),
            'description' => 'payment using behpardakht',
        ],
        'saman' => [
            'gateway' => Saman::class,
            'apiPurchaseUrl' => 'https://sep.shaparak.ir/Payments/InitPayment.asmx?WSDL',
            'apiPaymentUrl' => 'https://sep.shaparak.ir/payment.aspx',
            'apiVerificationUrl' => 'https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL',
            'apiRefundUrl' => 'https://srtm.sep.ir/RefundService/srvRefundV2.svc?wsdl',
            'merchantId' => env('SAMAN_MERCHANT_ID'),
            'transactionTermId' => env('SAMAN_TRANSACTION_TERM_ID'),
            'refundTermId' => env('SAMAN_REFUND_TERM_ID'),
            'username' => env('SAMAN_USERNAME'),
            'password' => env('SAMAN_PASSWORD'),
            'callbackUrl' => env('SAMAN_CALLBACK'),
            'description' => 'payment using saman',
        ],
    ],
];
