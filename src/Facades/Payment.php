<?php
namespace Celysium\Payment\Facades;

use Illuminate\Support\Facades\Facade;
use Celysium\Payment\Contracts\DriverInterface;

/**
 * @method static \Celysium\Payment\Payment id(string $id)
 * @method static \Celysium\Payment\Payment amount(int $amount)
 * @method static \Celysium\Payment\Payment transactionId(string $id)
 * @method static \Celysium\Payment\Payment via(string $driver)
 * @method static \Celysium\Payment\Payment callback(string $url)
 * @method DriverInterface gateway()
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'payment';
    }
}
