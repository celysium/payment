<?php

use Celysium\Payment\Controllers\LocalDriverController;
use Illuminate\Support\Facades\Route;

Route::get('local-payment/pay', [LocalDriverController::class, 'pay'])->name('local-driver.pay');
Route::get('local-payment/callback', [LocalDriverController::class, 'callback'])->name('local-driver.callback');
